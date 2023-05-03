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
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.html');
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
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
  
    // Render the "fields" template with the rows array
    $params = ['field' => $rows[0], 'edit' => false];
    return $view->render($response, 'single-field.html', $params);

});

// field edit
$app->get('/fields/{id}/edit', function (Request $request, Response $response, $args) use ($db, $twig) {

    // Query the "fields" table to get all the rows
    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    $view = Twig::fromRequest($request);

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

    $description = $data['description'];
    $shade_or_sun = $data['shade_or_sun'];
    $q = "UPDATE fields SET name = '$name', address = '$address', city = '$city', state = '$state', 
    zip = '$zip', multiple_sport_usage = '$multiple_sport_usage', shade_or_sun = '$shade_or_sun', sports_played = '$sports_played', turfgrass_species_present = '$turfgrass_species_present', description = '$description' WHERE id = $id"; 
    $stmnt = $db->exec($q);
  
    $view = Twig::fromRequest($request);
    $params = ['field' => $data, 'edit' => false, 'message' => $msg];
    return $view->render($response, 'single-field.html', $params);
});

// add report to field
$app->get('/fields/{id}/report', function (Request $request, Response $response, $args) use ($db, $twig) {

    // Query the "fields" table to get all the rows
    $results = $db->query('SELECT * FROM fields WHERE id = ' . $args['id']);
    $view = Twig::fromRequest($request);

    // select the single row
    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
  
    // Render the "fields" template with the rows array
    $params = ['field' => $rows[0], 'edit' => true];
    return $view->render($response, 'report.html', $params);

});



$app->run();

// Close the database connection
$db->close();
?>
