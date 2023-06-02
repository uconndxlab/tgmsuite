<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
require __DIR__ . '/../vendor/autoload.php';
// Create Twig
$twig = Twig::create('templates', ['cache' => false]);




// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

$app = AppFactory::create();

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));
// define base url



$app->get('/', function (Request $request, Response $response, $args) {
    // $view = Twig::fromRequest($request);
    // return $view->render($response, 'home.html');

    // redirect to fields
    return $response->withHeader('Location', '/fields')->withStatus(302);
});

function dd(...$vars) {
    foreach ($vars as $var) {
        var_dump($var);
    }
    die();
}


$app->get('/fields', function (Request $request, Response $response, $args) use ($db, $twig) {
    // Query the "fields" table to get all the rows
    $results = $db->query('SELECT * FROM fields');
    $view = Twig::fromRequest($request);

    // Convert the results into an array of associative arrays
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    // Render the "fields" template with the rows array
    $params = ['rows' => $rows];
    return $view->render($response, 'fields.html', $params);


});

// get the field data for a specific field

$app->get('/fields/{id}', function (Request $request, Response $response, $args) use ($db, $twig) {

   
    // Query the "fields" table to get all the rows
    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    $view = Twig::fromRequest($request);

    // select the single row
    $field = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $field[] = $row;
    }


    // get the reports for this field
    $results = $db->query('SELECT r.*, u.email
                      FROM reports AS r
                      JOIN users AS u ON r.evaluator_id = u.id
                      WHERE r.field_id = ' . $args['id'] .' 
                      ORDER BY r.evaluation_date DESC');

    $reports = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $reports[] = $row;
    }

    $params = ['field' => $field[0], 'reports' => $reports];


  
    // Render the "fields" template with the rows array
    return $view->render($response, 'single-field.html', $params);

});

// field edit
$app->get('/fields/{id}/edit', function (Request $request, Response $response, $args) use ($db, $twig) {

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
    return $view->render($response, 'single-field.html', $params);

});


/** post request for updating a field */

$app->post("/fields/{id}", function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $id = $args['id'];
    $data['id'] = $id;
    $name = $data['name'];
    $address = $data['address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];

    $multiple_sport_usage = $data['multiple_sport_usage'];

    // this is a comma delimited in the database, but should be an array in the form
    $sports_played = implode(",", $data['sports_played']);

    // this is a comma delimited in the database, but should be an array in the form
    $turfgrass_species_present = implode(',',$data['turfgrass_species_present']);

    $description = "field description";
    $shade_or_sun = $data['shade_or_sun'];
    $q = "UPDATE fields SET name = '$name', address = '$address', city = '$city', state = '$state', 
    zip = '$zip', multiple_sport_usage = '$multiple_sport_usage', shade_or_sun = '$shade_or_sun', sports_played = '$sports_played', turfgrass_species_present = '$turfgrass_species_present', description = '$description' WHERE id = $id"; 
    $stmnt = $db->exec($q);
    $msg = "Field Updated";
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];
    return $response->withHeader('Location', '/fields/' . $args['id'])->withStatus(302);

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


// route to save a color report for a field

$app->post('/fields/{id}/submit-color', function (Request $request, Response $response, $args) use ($db, $twig) {
    $data = $request->getParsedBody();
    $field_id = $args['id'];
    $date = date('Y-m-d');
    $evaluator_id = 1;

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
    $date = date('Y-m-d');
    $evaluator_id = 1;

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
    $date = date('Y-m-d');
    $evaluator_id = 1;

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
$app->get('/report/{id}/view', function (Request $request, Response $response, $args) use ($db, $twig) {

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
            $results = $db->query('SELECT * FROM fertilization_events WHERE report_id = ' . $args['id']);
            // select the single row
            $rows = [];
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            $fertilization_event = $rows[0];
            $field = $db->query('SELECT * FROM fields WHERE id = ' . $report['field_id'])->fetchArray(SQLITE3_ASSOC);
        break;
    }

    $content = $rows[0];
    

    $params = array(
        'report' => $report,
        'field' => $field,
        'content' => $content
    );

    $view = Twig::fromRequest($request);

    return $view->render($response, 'report.html', $params);

});

// route for /field/create
$app->get('/field/create', function (Request $request, Response $response, $args) use ($db, $twig) {

    $view = Twig::fromRequest($request);

    $params = array(
        'edit' => true,
        'message' => 'You are creating a new field.'
    );

    return $view->render($response, 'single-field.html', $params);

});




$app->run();

// Close the database connection
$db->close();
?>
