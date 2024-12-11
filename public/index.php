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

    //dd($data);

    $address = isset($data['address']) ? $data['address'] : '';
    $city = isset($data['city']) ? $data['city'] : '';
    $state = isset($data['state']) ? $data['state'] : '';
    $zip = isset($data['zip']) ? $data['zip'] : '';
    $color_rating = isset($data['color_rating']) ? $data['color_rating'] : '';
    $percent_shade = isset($data['percent_shade']) ? $data['percent_shade'] : '';
    $establishment_method = isset($data['establishment_method']) ? $data['establishment_method'] : '';
    $establishment_date = isset($data['establishment_date']) ? $data['establishment_date'] : '';

    // soil details (texutre, depth, condition)
    // soil texture must be an array so use implode
    $soil_texture = isset($data['soil_texture']) ? implode(",", $data['soil_texture']) : '';
    $soil_depth = isset($data['soil_depth']) ? $data['soil_depth'] : '';
    $soil_condition = isset($data['soil_condition']) ? $data['soil_condition'] : '';

    // renovation history details
    $percent_renovated = isset($data['percent_renovated']) ? $data['percent_renovated'] : '';
    $renovation_date = isset($data['renovation_date']) ? $data['renovation_date'] : '';
    $renovation_type = isset($data['renovation_type']) ? $data['renovation_type'] : '';

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
        soil_texture = '$soil_texture',
        soil_depth = '$soil_depth',
        soil_condition = '$soil_condition',
        percent_renovated = '$percent_renovated',
        renovation_date = '$renovation_date',
        renovation_type = '$renovation_type',
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
        soil_texture,
        percent_renovated,
        renovation_date,
        renovation_type,
        soil_depth,
        soil_condition,
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
        '$soil_texture',
        '$percent_renovated',
        '$renovation_date',
        '$renovation_type',
        '$soil_depth',
        '$soil_condition',
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





    $stmnt = $db->exec($q);


    // dd the latest row id




    // did the query work?
    if (!$stmnt) {
        $msg = "Error updating field";
    } else {
        $msg = "Field Updated";
    }


    $msg = "Field Updated";
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];



    // if id is 0, then this is a new field, so redirect to the new field by getting the last insert id
    if ($id == 0) {
        $id = $db->lastInsertRowID();

        // also update the field_users table with the current user id
        $user_id = $_SESSION['user_id'];
        $q = "INSERT INTO field_users (field_id, user_id, permission_level) VALUES ($id, $user_id, 'admin')";
        $stmnt = $db->exec($q);

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

/** submit cultivation report PLACEHOLDER */
$app->get('/fields/{id}/submit-cultivation', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Cultivation Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-cultivation.html', $params);
});

/** post route for cultivation PLACEHOLDER */
$app->post('/fields/{id}/submit-cultivation', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d', strtotime($data['date']));
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'cultivation')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO cultivation_reports (report_id, hollow_yes_no, hollow_notes, solid_yes_no, solid_notes, slice_yes_no, slice_notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['hollow']);
    $stmt->bindValue(3, $data['hollow_note']);
    $stmt->bindValue(4, $data['solid']);
    $stmt->bindValue(5, $data['solid_note']);
    $stmt->bindValue(6, $data['slice']);
    $stmt->bindValue(7, $data['slice_note']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Cultivation Report Submitted'];
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

/** submit pest management report */
$app->get('/fields/{id}/submit-pest', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Pest Management Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-pest.html', $params);
});

/** post route for pest management report */
$app->post('/fields/{id}/submit-pest', function (Request $request, Response $response, $args) use ($db, $twig) {



    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d', strtotime($data['date']));
    $evaluator_id = $_SESSION['user_id'];

    $broadleaf_dandelion = isset($data['broadleaf_dandelion']) ? 1 : 0;
    $broadleaf_dandelion_percent = isset($data['broadleaf_dandelion_percent']) ? $data['broadleaf_dandelion_percent'] : 0;
    $broadleaf_plantain = isset($data['broadleaf_plantain']) ? 1 : 0;
    $broadleaf_plantain_percent = isset($data['broadleaf_plantain_percent']) ? $data['broadleaf_plantain_percent'] : 0;
    $narrowleaf_plantain = isset($data['narrowleaf_plantain']) ? 1 : 0;
    $narrowleaf_plantain_percent = isset($data['narrowleaf_plantain_percent']) ? $data['narrowleaf_plantain_percent'] : 0;
    $heal_all = isset($data['heal_all']) ? 1 : 0;
    $heal_all_percent = isset($data['heal_all_percent']) ? $data['heal_all_percent'] : 0;
    $common_chickweed = isset($data['common_chickweed']) ? 1 : 0;
    $common_chickweed_percent = isset($data['common_chickweed_percent']) ? $data['common_chickweed_percent'] : 0;
    $oxalis = isset($data['oxalis']) ? 1 : 0;
    $oxalis_percent = isset($data['oxalis_percent']) ? $data['oxalis_percent'] : 0;
    $spurge = isset($data['spurge']) ? 1 : 0;
    $spurge_percent = isset($data['spurge_percent']) ? $data['spurge_percent'] : 0;
    $knotweed = isset($data['knotweed']) ? 1 : 0;
    $knotweed_percent = isset($data['knotweed_percent']) ? $data['knotweed_percent'] : 0;
    $ground_ivy = isset($data['ground_ivy']) ? 1 : 0;
    $ground_ivy_percent = isset($data['ground_ivy_percent']) ? $data['ground_ivy_percent'] : 0;
    $violet = isset($data['violet']) ? 1 : 0;
    $violet_percent = isset($data['violet_percent']) ? $data['violet_percent'] : 0;
    $mouse_ear_chickweed = isset($data['mouse_ear_chickweed']) ? 1 : 0;
    $mouse_ear_chickweed_percent = isset($data['mouse_ear_chickweed_percent']) ? $data['mouse_ear_chickweed_percent'] : 0;
    $clover_white = isset($data['clover_white']) ? 1 : 0;
    $clover_white_percent = isset($data['clover_white_percent']) ? $data['clover_white_percent'] : 0;
    $speedwell = isset($data['speedwell']) ? 1 : 0;
    $speedwell_percent = isset($data['speedwell_percent']) ? $data['speedwell_percent'] : 0;
    $other = isset($data['other']) ? 1 : 0;
    $other_percent = isset($data['other_percent']) ? $data['other_percent'] : 0;
    $broadleaf_control = isset($data['broadleaf_control']) ? $data['broadleaf_control'] : "None";

    $crabgrass = isset($data['crabgrass']) ? 1 : 0;
    $crabgrass_percent = isset($data['crabgrass_percent']) ? $data['crabgrass_percent'] : 0;
    $poa_annua = isset($data['poa_annua']) ? 1 : 0;
    $poa_annua_percent = isset($data['poa_annua_percent']) ? $data['poa_annua_percent'] : 0;

    $quackgrass = isset($data['quackgrass']) ? 1 : 0;
    $quackgrass_percent = isset($data['quackgrass_percent']) ? $data['quackgrass_percent'] : 0;
    $goosegrass = isset($data['goosegrass']) ? 1 : 0;
    $goosegrass_percent = isset($data['goosegrass_percent']) ? $data['goosegrass_percent'] : 0;
    $poa_trivialis = isset($data['poa_trivialis']) ? 1 : 0;
    $poa_trivialis_percent = isset($data['poa_trivialis_percent']) ? $data['poa_trivialis_percent'] : 0;
    $bentgrass = isset($data['bentgrass']) ? 1 : 0;
    $bentgrass_percent = isset($data['bentgrass_percent']) ? $data['bentgrass_percent'] : 0;
    $tall_fescue = isset($data['tall_fescue']) ? 1 : 0;
    $tall_fescue_percent = isset($data['tall_fescue_percent']) ? $data['tall_fescue_percent'] : 0;
    $yellow_nutsedge = isset($data['yellow_nutsedge']) ? 1 : 0;
    $yellow_nutsedge_percent = isset($data['yellow_nutsedge_percent']) ? $data['yellow_nutsedge_percent'] : 0;
    $orchardgrass = isset($data['orchardgrass']) ? 1 : 0;
    $orchardgrass_percent = isset($data['orchardgrass_percent']) ? $data['orchardgrass_percent'] : 0;
    $other_grasses = isset($data['other_grasses']) ? $data['other_grasses'] : 'None';
    $other_grasses_percent = isset($data['other_grasses_percent']) ? $data['other_grasses_percent'] : 0;
    $crabgrass_control = isset($data['crabgrass_control']) ? $data['crabgrass_control'] : 'None';

    $insects_grubs = isset($data['insects_grubs']) ? 1 : 0;
    $insects_grubs_type = isset($data['insects_grubs_type']) ? $data['insects_grubs_type'] : null;
    $insects_grubs_percent = isset($data['insects_grubs_percent']) ? $data['insects_grubs_percent'] : 0;

    $insects_sod_webworm = isset($data['insects_sod_webworm']) ? 1 : 0;
    $insects_sod_webworm_percent = isset($data['insects_sod_webworm_percent']) ? $data['insects_sod_webworm_percent'] : 0;

    $insects_chinch_bug = isset($data['insects_chinch_bug']) ? 1 : 0;
    $insects_chinch_bug_percent = isset($data['insects_chinch_bug_percent']) ? $data['insects_chinch_bug_percent'] : 0;

    $insects_billbug = isset($data['insects_billbug']) ? 1 : 0;
    $insects_billbug_percent = isset($data['insects_billbug_percent']) ? $data['insects_billbug_percent'] : 0;

    $other_insects = isset($data['other_insects']) ? 1 : 0;
    $other_insects_percent = isset($data['other_insects_percent']) ? $data['other_insects_percent'] : 0;

    $insects_control = isset($data['insects_control']) ? $data['insects_control'] : 'None';

    $disease_present = isset($data['disease_present']) ? $data['disease_present'] : 0;
    $disease_tall_fescue = isset($data['disease_tall_fescue']) ? $data['disease_tall_fescue'] : 0;

    $disease_perennial_ryegrass = isset($data['disease_perennial_ryegrass']) ? 1 : 0;
    $disease_kentucky_bluegrass = isset($data['disease_kentucky_bluegrass']) ? 1 : 0;
    $disease_fine_fescue = isset($data['disease_fine_fescue']) ? 1 : 0;
    $disease_other = isset($data['disease_other']) ? 1 : 0;
    $disease_percent = isset($data['disease_percent']) ? $data['disease_percent'] : 0;
    $disease_control = isset($data['disease_control']) ? $data['disease_control'] : 'None';




    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'pest')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();


    $q = "INSERT INTO pest_management_reports (
        report_id, broadleaf_dandelion, broadleaf_dandelion_percent, 
        broadleaf_plantain, broadleaf_plantain_percent, 
        narrowleaf_plantain, narrowleaf_plantain_percent, 
        heal_all, heal_all_percent, 
        common_chickweed, common_chickweed_percent, 
        oxalis, oxalis_percent, 
        spurge, spurge_percent, 
        knotweed, knotweed_percent, 
        ground_ivy, ground_ivy_percent, 
        violet, violet_percent, 
        mouse_ear_chickweed, mouse_ear_chickweed_percent, 
        clover_white, clover_white_percent, 
        speedwell, speedwell_percent, 
        other, other_percent, 
        broadleaf_control, 
        crabgrass, crabgrass_percent, 
        crabgrass_control, 
        poa_annua, poa_annua_percent, 
        quackgrass, quackgrass_percent, 
        goosegrass, goosegrass_percent, 
        poa_trivialis, poa_trivialis_percent, 
        bentgrass, bentgrass_percent, 
        tall_fescue, tall_fescue_percent, 
        yellow_nutsedge, yellow_nutsedge_percent, 
        orchardgrass, orchardgrass_percent, 
        other_grasses, other_grasses_percent, 
        insects_grubs, insects_grubs_type, 
        insects_grubs_percent, 
        insects_sod_webworm, insects_sod_webworm_percent, 
        insects_chinch_bug, insects_chinch_bug_percent, 
        insects_billbug, insects_billbug_percent, 
        other_insects, other_insects_percent, 
        insects_control, 
        disease_present, disease_tall_fescue, 
        disease_perennial_ryegrass, 
        disease_kentucky_bluegrass, 
        disease_fine_fescue, 
        disease_other, disease_percent, 
        disease_control
    ) VALUES (
        ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?,
        ?, ?
    )";

    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $broadleaf_dandelion);
    $stmt->bindValue(3, $broadleaf_dandelion_percent);
    $stmt->bindValue(4, $broadleaf_plantain);
    $stmt->bindValue(5, $broadleaf_plantain_percent);
    $stmt->bindValue(6, $narrowleaf_plantain);
    $stmt->bindValue(7, $narrowleaf_plantain_percent);
    $stmt->bindValue(8, $heal_all);
    $stmt->bindValue(9, $heal_all_percent);
    $stmt->bindValue(10, $common_chickweed);
    $stmt->bindValue(11, $common_chickweed_percent);
    $stmt->bindValue(12, $oxalis);
    $stmt->bindValue(13, $oxalis_percent);
    $stmt->bindValue(14, $spurge);
    $stmt->bindValue(15, $spurge_percent);
    $stmt->bindValue(16, $knotweed);
    $stmt->bindValue(17, $knotweed_percent);
    $stmt->bindValue(18, $ground_ivy);
    $stmt->bindValue(19, $ground_ivy_percent);
    $stmt->bindValue(20, $violet);
    $stmt->bindValue(21, $violet_percent);
    $stmt->bindValue(22, $mouse_ear_chickweed);
    $stmt->bindValue(23, $mouse_ear_chickweed_percent);
    $stmt->bindValue(24, $clover_white);
    $stmt->bindValue(25, $clover_white_percent);
    $stmt->bindValue(26, $speedwell);
    $stmt->bindValue(27, $speedwell_percent);
    $stmt->bindValue(28, $other);
    $stmt->bindValue(29, $other_percent);
    $stmt->bindValue(30, $broadleaf_control);
    $stmt->bindValue(31, $crabgrass);
    $stmt->bindValue(32, $crabgrass_percent);
    $stmt->bindValue(33, $crabgrass_control);
    $stmt->bindValue(34, $poa_annua);
    $stmt->bindValue(35, $poa_annua_percent);
    $stmt->bindValue(36, $quackgrass);
    $stmt->bindValue(37, $quackgrass_percent);
    $stmt->bindValue(38, $goosegrass);
    $stmt->bindValue(39, $goosegrass_percent);
    $stmt->bindValue(40, $poa_trivialis);
    $stmt->bindValue(41, $poa_trivialis_percent);
    $stmt->bindValue(42, $bentgrass);
    $stmt->bindValue(43, $bentgrass_percent);
    $stmt->bindValue(44, $tall_fescue);
    $stmt->bindValue(45, $tall_fescue_percent);
    $stmt->bindValue(46, $yellow_nutsedge);
    $stmt->bindValue(47, $yellow_nutsedge_percent);
    $stmt->bindValue(48, $orchardgrass);
    $stmt->bindValue(49, $orchardgrass_percent);
    $stmt->bindValue(50, $other_grasses);
    $stmt->bindValue(51, $other_grasses_percent);
    $stmt->bindValue(52, $insects_grubs);
    $stmt->bindValue(53, $insects_grubs_type);
    $stmt->bindValue(54, $insects_grubs_percent);
    $stmt->bindValue(55, $insects_sod_webworm);
    $stmt->bindValue(56, $insects_sod_webworm_percent);

    $stmt->bindValue(57, $insects_chinch_bug);
    $stmt->bindValue(58, $insects_chinch_bug_percent);
    $stmt->bindValue(59, $insects_billbug);
    $stmt->bindValue(60, $insects_billbug_percent);
    $stmt->bindValue(61, $other_insects);
    $stmt->bindValue(62, $other_insects_percent);
    $stmt->bindValue(63, $insects_control);
    $stmt->bindValue(64, $disease_present);
    $stmt->bindValue(65, $disease_tall_fescue);
    $stmt->bindValue(66, $disease_perennial_ryegrass);
    $stmt->bindValue(67, $disease_kentucky_bluegrass);
    $stmt->bindValue(68, $disease_fine_fescue);
    $stmt->bindValue(69, $disease_other);
    $stmt->bindValue(70, $disease_percent);
    $stmt->bindValue(71, $disease_control);

    if ($stmt->execute()) {
        $msg = "Pest Management Report Saved";
    } else {
        $msg = "Failed to save Pest Management Report";
    }

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Pest Management Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

/** submit thatch accumulation report  */
$app->get('/fields/{id}/submit-thatch', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Thatch Accumulation Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-thatch.html', $params);
});

/** post route for thatch accumulation PLACEHOLDER */
$app->post('/fields/{id}/submit-thatch', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    // now
    $date = date('Y-m-d');
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'thatch_accumulation')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO thatch_accumulation_reports (report_id, thatch_accumulation) VALUES ( ?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['thatch_accumulation']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Thatch Accumulation Report Submitted'];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);
});

/** submit soil test report */
$app->get('/fields/{id}/submit-soil', function (Request $request, Response $response, $args) use ($db, $twig) {

    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    $params = array(
        'field' => $rows[0],
        'field_id' => $args['id'],
        'form_type' => 'Soil Test Report'
    );

    $view = Twig::fromRequest($request);

    // Render the "fields" template with the rows array

    return $view->render($response, 'submit-soil.html', $params);
});

/** post route for soil test */
$app->post('/fields/{id}/submit-soil', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d', strtotime($data['date']));
    $evaluator_id = $_SESSION['user_id'];

    $q = "INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES (?, ?, ?, 'soil_test')";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $date);
    $stmt->bindValue(2, $evaluator_id);
    $stmt->bindValue(3, $field_id);
    $stmt->execute();
    $report_id = $db->lastInsertRowId();

    $q = "INSERT INTO soil_test (report_id, action_taken) VALUES (?, ?)";
    $stmt = $db->prepare($q);
    $stmt->bindValue(1, $report_id);
    $stmt->bindValue(2, $data['action_taken']);
    $stmt->execute();

    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => 'Soil Test Report Submitted'];
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
                // add color description to the row based on the color_option
                $color = '';
                switch ($row['color_option']) {
                    case 'TD':
                        $color = 'Turf Dormant';
                        break;
                    case '1':
                        $color = '1 - Yellow Green';
                        break;
                    case '2':
                        $color = '2 - Light Green';
                        break;

                    case '3':
                        $color = '3 - Med/Light Green';
                        break;

                    case '4':
                        $color = '4 - Medium Green';
                        break;

                    case '5':
                        $color = '5 - Dark Green';
                        break;
                }
                $row['color'] = $color;

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

        case 'cultivation':
            $results = $db->query('SELECT * FROM cultivation_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $cultication_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;

        case 'pest':
            // dd("Pest management");
            $results = $db->query('SELECT * FROM pest_management_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $pest_management_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;

        case 'thatch_accumulation':
            $results = $db->query('SELECT * FROM thatch_accumulation_reports WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $thatch_accumulation_report = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
            break;

        case 'soil_test':
            $results = $db->query('SELECT * FROM soil_test WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $soil_test_report = $rows[0];
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
