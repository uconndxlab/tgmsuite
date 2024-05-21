<?php
session_start();

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';
require 'AuthMiddleware.php';

// Create Twig
$twig = Twig::create('templates', ['cache' => false]);




// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

$app = AppFactory::create();

// Add the authentication middleware to the app
$authMiddleware = new AuthMiddleware();
$isAuthenticated = $authMiddleware->isAuthenticated();

function dd(...$vars)
{
    foreach ($vars as $var) {
        var_dump($var);
    }
    die();
}



if ($isAuthenticated) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = $db->query($sql);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $user['id'] = $row['id'];
    $user['name'] = $row['name'] . " yeep";
    $user['email'] = $row['email'];

    $auth_info = array('is_authenticated' => $isAuthenticated, 'user_id' => $_SESSION['user_id'], 'user' => $user['email'], 'name' => $user['name']);
} else {
    $auth_info = array('is_authenticated' => $isAuthenticated, 'user_id' => 0);
}


// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response, $args) {


    // redirect to home
    return $response->withHeader('Location', '/home')->withStatus(302);
});

$app->get('/home', function (Request $request, Response $response, $args) use ($db, $twig, $auth_info) {

    // load the home template
    $view = Twig::fromRequest($request);
    $params = array('auth_info' => $auth_info);

    if ($auth_info['is_authenticated']) {
        header('Location: /fields');
    } else {
        return $view->render($response, 'home.html', $params);
    }
});



$app->get('/fields', function (Request $request, Response $response, $args) use ($db, $twig, $isAuthenticated, $auth_info) {


    // get the fields, but pivot on field_users to get the fields for the current user id

    $user_id = $_SESSION['user_id'];

    $query_string = "SELECT f.*, uf.permission_level FROM fields AS f JOIN field_users AS uf ON f.id = uf.field_id WHERE uf.user_id = $user_id";


    // Query the "fields" table to get all the rows
    $results = $db->query($query_string);

    $view = Twig::fromRequest($request);

    // Convert the results into an array of associative arrays
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    // Render the "fields" template with the rows array
    $params = ['rows' => $rows];
    $params['auth_info'] = $auth_info;
    return $view->render($response, 'fields.html', $params);
})->add($authMiddleware);


// get the field data for a specific field

$app->get('/fields/{id}', function (Request $request, Response $response, $args) use ($db, $twig, $isAuthenticated, $auth_info) {

    $user_id = $auth_info['user_id'];
    // Query the "fields" table to get all the rows and make sure the user has access to this field
    $query_string = "SELECT f.*, uf.permission_level FROM fields AS f JOIN field_users AS uf ON f.id = uf.field_id WHERE uf.user_id = $user_id AND f.id = " . $args['id'];
 
    $results = $db->query($query_string);


    //$results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);



    $view = Twig::fromRequest($request);

    // select the single row
    $field = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $field[] = $row;
    }



    // get the reports for this field
    if (isset($_GET['date']) || isset($_GET['type'])) {
        $date = $_GET['date'] ?? null;
        $type = $_GET['type'] ?? null;
        $query = 'SELECT r.*, u.email
                  FROM reports AS r
                  JOIN users AS u ON r.evaluator_id = u.id
                  WHERE r.field_id = ' . $args['id'];
        if ($date) {
            $query .= ' AND r.evaluation_date = "' . $date . '"';
        }
        if ($type) {
            $query .= ' AND r.type = "' . $type . '"';
        }
        $query .= ' ORDER BY r.evaluation_date DESC';
        $results = $db->query($query);
    } else {
        $results = $db->query('SELECT r.*, u.email
                              FROM reports AS r
                              JOIN users AS u ON r.evaluator_id = u.id
                              WHERE r.field_id = ' . $args['id'] . ' 
                              ORDER BY r.evaluation_date DESC');
    }

    $reports = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $reports[] = $row;
    }

    if (count($field) == 0) {
        return $view->render($response, '404.html');
    }

    $params = ['field' => $field[0], 'reports' => $reports];
    $params['auth_info'] = $auth_info;



    // Render the "fields" template with the rows array
    return $view->render($response, 'single-field.html', $params);
});

// field edit
$app->get('/fields/{id}/edit', function (Request $request, Response $response, $args) use ($db, $twig, $auth_info) {

    // Query the "fields" table to get all the rows
    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    $view = Twig::fromRequest($request);

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }



    // Render the "fields" template with the rows array
    $params = ['field' => $rows[0], 'edit' => true];
    $params['auth_info'] = $auth_info;
    return $view->render($response, 'single-field.html', $params);
});


/** post request for updating a field */

$app->post("/fields/{id}", function (Request $request, Response $response, $args) use ($db, $twig, $auth_info) {

    $data = $request->getParsedBody();
    $id = $args['id'];
    $data['id'] = $id;
    $name = $data['name'];
    // $name might have a single quote in it, so we need to escape it
    $name = str_replace("'", "''", $name);

    

    $address = isset($data['address']) ? $data['address'] : '';
    $city = isset($data['city']) ? $data['city'] : '';
    $state = isset($data['state']) ? $data['state'] : '';
    $zip = isset($data['zip']) ? $data['zip'] : '';
    $color_rating = isset($data['color_rating']) ? $data['color_rating'] : '';
    $percent_shade = isset($data['percent_shade']) ? $data['percent_shade'] : '';
    $establishment_method = isset($data['establishment_method']) ? $data['establishment_method'] : '';
    $establishment_date = isset($data['establishment_date']) ? $data['establishment_date'] : '';

    $irrigation_system = isset($data['irrigation_system']) ? $data['irrigation_system'] : '';
    
    // water source is a comma delimited in the database, but should be an array in the form
    $water_source = isset($data['water_source']) ? implode(",", $data['water_source']) : '';

    $irrigation_frequency = isset($data['irrigation_frequency']) ? $data['irrigation_frequency'] : '';
    $portable_system = isset($data['portable_system']) ? $data['portable_system'] : '';
    $wetting_agents = isset($data['wetting_agents']) ? $data['wetting_agents'] : '';

    $multiple_sport_usage = isset($data['multiple_sport_usage']) ? $data['multiple_sport_usage'] : '';

    // this is a comma delimited in the database, but should be an array in the form
    $sports_played = isset($data['sports_played']) ? implode(",", $data['sports_played']) : '';

    // this is a comma delimited in the database, but should be an array in the form
    $turfgrass_species_present = isset($data['turfgrass_species_present']) ? implode(',', $data['turfgrass_species_present']) : '';

    $mowing_frequency = isset($data['mowing_frequency']) ? $data['mowing_frequency'] : '';
    $mowing_height = isset($data['mowing_height']) ? $data['mowing_height'] : '';
    $mowing_method = isset($data['mowing_method']) ? $data['mowing_method'] : '';
    $pgrs_used = isset($data['pgrs_used']) ? $data['pgrs_used'] : '';

    $description = "field description";
    $shade_or_sun = isset($data['shade_or_sun']) ? $data['shade_or_sun'] : '';

    if ($id != 0) {
        $q = "UPDATE fields SET 
        name = '$name', 
        address = '$address', 
        city = '$city', state = '$state', 
        zip = '$zip', 
        multiple_sport_usage = '$multiple_sport_usage', 
        shade_or_sun = '$shade_or_sun', 
        sports_played = '$sports_played', 
        turfgrass_species_present = '$turfgrass_species_present', 
        establishment_method = '$establishment_method',
        establishment_date = '$establishment_date',
        color_rating = '$color_rating' , 
        percent_shade = '$percent_shade',
        irrigation_system = '$irrigation_system',
        water_source = '$water_source',
        irrigation_frequency = '$irrigation_frequency',
        portable_system = '$portable_system',
        wetting_agents = '$wetting_agents',
        mowing_frequency = '$mowing_frequency',
        mowing_height = '$mowing_height',
        mowing_method = '$mowing_method',
        pgrs_used = '$pgrs_used',
        description = '$description' 
        WHERE id = $id";
    } else {
        // insert query instead of update
        $q = "INSERT INTO fields (name, 
        address, 
        city, 
        state, 
        zip, 
        multiple_sport_usage, 
        shade_or_sun, 
        sports_played, 
        turfgrass_species_present, 
        establishment_method,
        establishment_date,
        color_rating, 
        percent_shade, 
        irrigation_system, 
        water_source, 
        irrigation_frequency, 
        portable_system, 
        wetting_agents, 
        mowing_frequency,
        mowing_height,
        mowing_method,
        pgrs_used,
        description) VALUES ('$name', 
        '$address', 
        '$city', 
        '$state', 
        '$zip', 
        '$multiple_sport_usage', 
        '$shade_or_sun', 
        '$sports_played', 
        '$turfgrass_species_present', 
        '$establishment_method',
        '$establishment_date',
        '$color_rating', 
        '$percent_shade', 
        '$irrigation_system', 
        '$water_source', 
        '$irrigation_frequency', 
        '$portable_system', 
        '$wetting_agents', 
        '$mowing_frequency',
        '$mowing_height',
        '$mowing_method',
        '$pgrs_used',
        '$description')";
    }

    //dd($q);


    $stmnt = $db->exec($q);

    // store the last insert id for later
    $new_id = $db->lastInsertRowID();

    $current_user_id = $auth_info['user_id'];

    // also insert into the field_users table
    $q = "INSERT INTO field_users (user_id, field_id, permission_level) VALUES (
        $current_user_id
        , $new_id, 'owner')";
    $stmnt = $db->exec($q);


    // did the query work?
    if (!$stmnt) {
        $msg = "Error updating field";
    } else {
        $msg = "Field Updated";
    }


    $msg = "Field Updated";
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];
    echo $new_id;


    // if id is 0, then this is a new field, so redirect to the new field by getting the last insert id
    if ($id == 0) {
        $id = $new_id;

        return $response->withHeader('Location', '/fields/' . $id)->withStatus(302);
    } else {

        return $response->withHeader('Location', '/fields/' . $id)->withStatus(302);
    }
});

// route to register a new user via the form
$app->post('/user/register', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $email = $data['email'];
    $name = $data['username'];
    $password = $data['password'];
    $password_confirm = $data['password_confirm'];

    // check if the passwords match... and if they don't... redirect to the login page
    if ($password != $password_confirm) {
        $msg = "Passwords do not match";
        $view = Twig::fromRequest($request);
        $params = ['field' => $data, 'edit' => false, 'message' => $msg];
        return $view->render($response, 'home.html', $params);
    }

    // check if the email is already in the database
    $results = $db->query("SELECT * FROM users WHERE email = '$email'");
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    // if the email is already in the database, then redirect to the login page
    if (count($rows) > 0) {
        $msg = "Email already in use";
        $view = Twig::fromRequest($request);
        $params = ['field' => $data, 'edit' => false, 'message' => $msg];
        return $view->render($response, 'home.html', $params);
    }

    // if the email is not in the database, then insert the user into the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $q = "INSERT INTO users (name,email, password) VALUES ('$name', '$email', '$hashedPassword')";

    $stmnt = $db->exec($q);

    // get last insert id
    $id = $db->lastInsertRowID();

    // start a session
    $_SESSION['user_id'] = $id;


    $msg = "User Registered: " . $email;
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];

    // redirect to /fields if the user is logged in
    return $response->withHeader('Location', '/fields')->withStatus(302);
});

$app->get("/logout", function (Request $request, Response $response, $args) use ($db, $twig) {
    session_destroy();
    return $response->withHeader('Location', '/home')->withStatus(302);
})->add($authMiddleware);

// route to login a user via the form
$app->post('/user/login', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $email = $data['email'];
    $password = $data['password'];

    // check if the email is already in the database
    $results = $db->query("SELECT * FROM users WHERE email = '$email'");
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    // if the email is not in the database, then redirect to the login page
    if (count($rows) == 0) {
        $msg = "Email not found";
        $view = Twig::fromRequest($request);
        $params = ['field' => $data, 'edit' => false, 'message' => $msg];
        return $view->render($response, 'home.html', $params);
    }

    // if the email is in the database, then check the password
    $user = $rows[0];
    if (!password_verify($password, $user['password'])) {
        $msg = "Password incorrect";
        $view = Twig::fromRequest($request);
        $params = ['field' => $data, 'edit' => false, 'message' => $msg];
        return $view->render($response, 'home.html', $params);
    }

    // if the password is correct, then log the user in

    $_SESSION['user_id'] = $user['id'];
    $msg = "User Logged In: " . $user['email'];
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];

    // redirect to /fields if the user is logged in
    return $response->withHeader('Location', '/fields')->withStatus(302);
});

// route to delete a field
$app->post('/fields/{id}/delete', function (Request $request, Response $response, $args) use ($db, $twig) {

    // sanitize the args
    $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);

    // delete the field
    $db->exec("DELETE FROM fields WHERE id = $id");
    // delete all the relationships to this field
    $db->exec("DELETE FROM reports WHERE field_id = $id");
    $db->exec("DELETE FROM evaluations WHERE field_id = $id");
    $db->exec("DELETE FROM color_reports WHERE field_id = $id");
    $db->exec("DELETE FROM photos WHERE field_id = $id");
    $db->exec("DELETE FROM fertilization_events WHERE field_id = $id");


    return $response->withHeader('Location', '/fields')->withStatus(302);
});

// route to delete a report
$app->post('/report/{id}/delete', function (Request $request, Response $response, $args) use ($db, $twig) {

    // sanitize the args
    $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);

    // get the field id associated with this report
    $results = $db->query('SELECT * FROM reports WHERE id = ' . $id);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    // get the field id
    $field_id = $rows[0]['field_id'];

    // delete the report
    $db->exec("DELETE FROM reports WHERE id = $id");
    // delete all the relationships to this report
    $db->exec("DELETE FROM evaluations WHERE report_id = $id");
    $db->exec("DELETE FROM color_reports WHERE report_id = $id");
    $db->exec("DELETE FROM photos WHERE report_id = $id");
    $db->exec("DELETE FROM fertilization_events WHERE report_id = $id");
    return $response->withHeader('Location', '/fields/' . $field_id)->withStatus(302);
});

// route to conduct a turf rating
$app->get('/fields/{id}/quality-checklist', function (Request $request, Response $response, $args) use ($db, $twig) {


    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Quality Checklist'
    );

    $view = Twig::fromRequest($request);




    // Render the "fields" template with the rows array

    return $view->render($response, 'quality-checklist.html', $params);
});

// route to submit a photo for a field
$app->get('/fields/{id}/submit-photo', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Submit Photo'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-photo.html', $params);
});

// route to submit a topdressing report for a field

$app->get('/fields/{id}/submit-topdressing', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Topdressing Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-topdressing.html', $params);
});

$app->get('/fields/{id}/submit-fertilization', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Fertilization Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-fert.html', $params);
});

$app->post('/fields/{id}/submit-fertilization', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d', strtotime($data['date']));
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'fertilization')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO fertilization_reports (report_id, product, rate, npk, compost, bio_stimulant) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['product']);
    $stmt->bindValue(3, $data['rate']);
    $stmt->bindValue(4, $data['npk']);
    $stmt->bindValue(5, $data['compost']);
    $stmt->bindValue(6, $data['biostimulant']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Fertilization Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

// route to submit a color report for a field

$app->get('/fields/{id}/submit-color', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Color Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-color.html', $params);
});

/** submit overseeding report */
$app->get('/fields/{id}/submit-overseeding', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Overseeding Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-overseeding.html', $params);
});

/** post route for overseeding */
$app->post('/fields/{id}/submit-overseeding', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d', strtotime($data['date']));
    $evaluator_id = $_SESSION['user_id'];

    // species is a comma delimited in the database, but should be an array in the form
    $species = implode(",", $data['species']);

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'overseeding')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO overseed_reports (report_id, rate, formula, pre_germ, species) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['rate']);
    $stmt->bindValue(3, $data['formula']);
    $stmt->bindValue(4, $data['pre_germ']);
    $stmt->bindValue(5, $species);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Overseeding Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

// post route to save topdressing report for a field

$app->post('/fields/{id}/submit-topdressing', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
   
    // there should be a date field in the form ($args['topdressing_date']), which should be a date string and should be converted to a date object
    $date = $data['topdressing_date'];
    $date = date('Y-m-d', strtotime($date));

    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'topdressing')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);

    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO topdressing_reports (report_id, topdressing_rate, topdressing_description) VALUES (?, ?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['topdressing_rate']);
    $stmt->bindValue(3, $data['topdressing_composition']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Topdressing Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

// route to save a color report for a field

$app->post('/fields/{id}/submit-color', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d H:ia');
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'color')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO color_reports (report_id, color_option) VALUES (?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['color']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Color Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});


// route to save a photo for a field

$app->post('/fields/{id}/submit-photo', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    $field_id = $args['id'];
    $date = date('Y-m-d H:ia');
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'photo')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    // Handle the uploaded photo file
    $uploadedFile = $uploadedFiles['photo'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = $uploadedFile->getClientFilename();
        $targetPath = "uploads/" . $filename;
        $uploadedFile->moveTo($targetPath);

        // Save the photo URL to the database
        $photo = "uploads/" . $filename;
        $q = "INSERT INTO photos (report_id, photo_url) VALUES (?, ?)";
        $stmt = $db->prepare($q);
        $stmt->bindValue(1, $report_id);
        $stmt->bindValue(2, $photo);
        $result = $stmt->execute();

        $msg = "Photo Saved";
    } else {


        $errorCode = $uploadedFile->getError();
        $msg = "Failed to upload photo";
        // get the error code


        $msg .= " Error code: $errorCode";
    }


    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});




// save a turf rating
$app->post('/fields/{id}/quality-checklist', function (Request $request, Response $response, $args) use ($db, $twig) {

    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d H:ia');
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES ('$date', $evaluator_id, $field_id, 'evaluation')";
    $db->exec($q);

    $report_id = $db->lastInsertRowID();

    // report_id INTEGER,
    // turf_density INTEGER,
    // smoothness_rating INTEGER,
    // weeds_rating INTEGER,
    // stones_at_surface INTEGER,
    // depressions INTEGER,
    // turf_rating INTEGER,
    // surface_rating INTEGER

    $turf_density = $data['turfDensity'];
    $smoothness_rating = $data['smoothness'];
    $weeds_rating = $data['weedsPercentage'];
    $stones_at_surface = $data['stonesAtSurface'];
    $depressions = $data['depressions'];
    $turf_rating = $data['turfRating'];
    $surface_rating = $data['surfaceRating'];
    $overall_rating = $turf_rating - $surface_rating;

    $db->exec("INSERT INTO evaluations (report_id, turf_density, smoothness_rating, weeds_rating, stones_at_surface, depressions, turf_rating, surface_rating, overall_rating) VALUES ($report_id, $turf_density, $smoothness_rating, $weeds_rating, $stones_at_surface, $depressions, $turf_rating, $surface_rating, $overall_rating)");



    $msg = "Turf Rating Saved";

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Quality Checklist',
        'message' => $msg
    );


    // redirect to /fields/{id}
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

// route for /report/{id}/view
$app->get('/report/{id}/view', function (Request $request, Response $response, $args) use ($db, $twig, $auth_info) {

    $results = $db->query('SELECT * FROM reports WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $report = $rows[0];

    // switch on the type of report (evaluation, color_report, photo, or fertilization_event)
    switch ($report['type']) {
        case 'evaluation':
            $results = $db->query('SELECT * FROM evaluations WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $evaluation = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;
        case 'color':
            $results = $db->query('SELECT * FROM color_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $color_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;
        case 'photo':
            $results = $db->query('SELECT * FROM photos WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $photo = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;
        case 'fertilization':
            $results = $db->query('SELECT * FROM fertilization_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $fertilization_event = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;

        case 'topdressing':
            $results = $db->query('SELECT * FROM topdressing_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $topdressing_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;

        case 'overseeding':
            $results = $db->query('SELECT * FROM overseed_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $overseeding_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;
    }

    $content = $rows[0];


    $params = array(
        'report' => $report,
        'field' => $field,
        'content' => $content,
        'auth_info' => $auth_info
    );

    $view = Twig::fromRequest($request);

    return $view->render($response, 'report.html', $params);
});

// route for /field/create
$app->get('/field/create', function (Request $request, Response $response, $args) use ($db, $twig, $auth_info) {

    $view = Twig::fromRequest($request);

    $params = array(
        'edit' => true,
        'message' => 'You are creating a new field.',
        'field' => array('id' => 0),
        'auth_info' => $auth_info
    );

    return $view->render($response, 'single-field.html', $params);
})->add($authMiddleware);



$app->run();

// Close the database connection
$db->close();
