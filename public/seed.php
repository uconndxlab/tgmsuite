<?php
// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

// Create the "fields" table: id, name, address, city, state, zip, multiple sport usage (yes/no), sports played, turfgrass species present,
//, and description
    
    // irrigation_system = '$irrigation_system',
    // water_source = '$water_source',
    // irrigation_frequency = '$irrigation_frequency',
    // portable_system = '$portable_system',
    // wetting_agents = '$wetting_agents',
 

$db->exec('CREATE TABLE IF NOT EXISTS fields (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    address TEXT,
    city TEXT,
    state TEXT,
    zip TEXT,
    multiple_sport_usage TEXT,
    sports_played TEXT,
    turfgrass_species_present TEXT,
    establishment_method TEXT,
    establishment_date TEXT,
    shade_or_sun TEXT,
    percent_shade TEXT,
    color_rating TEXT,
    irrigation_system TEXT,
    water_source TEXT,
    irrigation_frequency TEXT,
    portable_system TEXT,
    wetting_agents TEXT,
    mowing_height TEXT,
    mowing_frequency TEXT,
    pgrs_used TEXT,
    mowing_method TEXT,
    description TEXT
)');



//** Create the evaluation table with turf rating and surface rating */

// Create a table for reports with id, evaluation_date, evaluator_id, field_id, and type (evaluation, photo, color, fertilization)

$db->exec('CREATE TABLE IF NOT EXISTS reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evaluation_date DATE,
    evaluator_id INTEGER,
    field_id INTEGER,
    type TEXT,
    FOREIGN KEY (field_id) REFERENCES fields(id)
    FOREIGN KEY (evaluator_id) REFERENCES users(id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS evaluations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    turf_density INTEGER,
    smoothness_rating INTEGER,
    weeds_rating INTEGER,
    stones_at_surface INTEGER,
    depressions INTEGER,
    turf_rating INTEGER,
    surface_rating INTEGER,
    overall_rating INTEGER,
    FOREIGN KEY (report_id) REFERENCES reports(id)
)');

/** Create Fertilization Event Table */

$db->exec('CREATE TABLE IF NOT EXISTS fertilization_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    fertilizer_type TEXT,
    fertilizer_rate INTEGER,
    fertilizer_description TEXT,
    FOREIGN KEY (report_id) REFERENCES reports(id)
)');

/** Create Photo Table (evaluation_date, evaluator_id, field_id, photo_url) */
$db->exec('CREATE TABLE IF NOT EXISTS photos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    photo_url TEXT,
    FOREIGN KEY (report_id) REFERENCES reports(id)
)');


/** Create a Color table with date, evaluator_id, field_id, and color option (Dark Green 5, Med Green 4, Med/Light Green 3, Light Green 2, Yellow Green 1, Turf Dormant TD)
  */

$db->exec('CREATE TABLE IF NOT EXISTS color_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    color_option TEXT,
    FOREIGN KEY (report_id) REFERENCES reports(id)
)');

/** Create the Topdressing_reports table */
$db->exec('CREATE TABLE IF NOT EXISTS topdressing_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    topdressing_rate TEXT,
    topdressing_description TEXT,
    FOREIGN KEY (report_id) REFERENCES reports(id)
)');


//** Create the users table with name, email, and password */

$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    email TEXT,
    password TEXT
)');

/** create the field/users access table */

$db->exec('CREATE TABLE IF NOT EXISTS field_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    field_id INTEGER,
    user_id INTEGER,
    permission_level INTEGER,
    FOREIGN KEY (field_id) REFERENCES fields(id)
    FOREIGN KEY (user_id) REFERENCES users(id)
)');


/** Seed the fields table with some sample data */
$db->exec('INSERT INTO fields (name, address, city, state, zip, multiple_sport_usage, sports_played, turfgrass_species_present, establishment_method, establishment_date, shade_or_sun, percent_shade, color_rating, irrigation_system, water_source, irrigation_frequency, portable_system, wetting_agents, mowing_height, mowing_frequency, mowing_method, description) VALUES ("Field 1", "123 Main St", "Anytown", "NY", "12345", "Yes", "Soccer, Football", "Kentucky Bluegrass, Perennial Ryegrass", "Sod", "2020-01-01", "Sun", "0", "Dark Green 5", "Yes", "City Water", "3 times per week", "No", "No", "2.5 inches", "3 times per week", "rotary", "This is a description of the field")');

/** Seed the users table with some sample data */
$db->exec('INSERT INTO users (name, email, password) VALUES ("John Doe", "john@doefamily.org", "password")');

/** Seed the field_users table with some sample data */
/** Permission levels can be 1 for read, 2 for rw */
$db->exec('INSERT INTO field_users (field_id, user_id, permission_level) VALUES (1, 1, 2)');

/** Seed the reports table with some sample data */
$db->exec('INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES ("2020-01-01", 1, 1, "evaluation")');
$db->exec('INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES ("2020-01-02", 1, 1, "photo")');
$db->exec('INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES ("2020-01-03", 1, 1, "color")');
$db->exec('INSERT INTO reports (evaluation_date, evaluator_id, field_id, type) VALUES ("2020-01-04", 1, 1, "fertilization")');

/** Seed the evaluations table with some sample data */
$db->exec('INSERT INTO evaluations (report_id, turf_density, smoothness_rating, weeds_rating, stones_at_surface, depressions, turf_rating, surface_rating, overall_rating) VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1)');
/** Seed the photos table with some sample data */
$db->exec('INSERT INTO photos (report_id, photo_url) VALUES (2, "https://via.placeholder.com/150")');

/** Seed the color_reports table with some sample data */
$db->exec('INSERT INTO color_reports (report_id, color_option) VALUES (3, "Dark Green 5")');

/** Seed the fertilization_events table with some sample data */
$db->exec('INSERT INTO fertilization_events (report_id, fertilizer_type, fertilizer_rate, fertilizer_description) VALUES (4, "Nitrogen", 1, "This is a description of the fertilization event")');



// Close the database connection
$db->close();