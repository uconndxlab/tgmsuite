<?php
// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

// Create the "fields" table: id, name, address, city, state, zip, multiple sport usage (yes/no), sports played, turfgrass species present,
//, and description

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
    establishment_age TEXT,
    shade_or_sun TEXT,
    percent_shade TEXT,

    description TEXT
)');


//** Create the evaluation table with turf rating and surface rating */

$db->exec('CREATE TABLE IF NOT EXISTS evaluations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evaluation_date DATE,
    evaluator_id INTEGER,
    field_id INTEGER,
    percent_turf_covered INTEGER,
    percent_weeds INTEGER,
    stones_at_surface INTEGER,
    depressions INTEGER,
    turf_rating INTEGER,
    surface_rating INTEGER,
    FOREIGN KEY (field_id) REFERENCES fields(id)
    FOREIGN KEY (evaluator_id) REFERENCES users(id)
)');

/** Create Fertilization Event Table */

$db->exec('CREATE TABLE IF NOT EXISTS fertilization_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    field_id INTEGER,
    date DATE,
    fertilizer_type TEXT,
    fertilizer_rate INTEGER,
    fertilizer_description TEXT,
    FOREIGN KEY (field_id) REFERENCES fields(id)
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
    FOREIGN KEY (field_id) REFERENCES fields(id)
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

/** Seed the fields table with some sample data */
$db->exec('INSERT INTO fields (name, address, city, state, zip, multiple_sport_usage, sports_played, turfgrass_species_present, establishment_method, establishment_age, shade_or_sun, percent_shade, description) VALUES ("Field 1", "123 Main St", "Anytown", "NY", "12345", "Yes", "Football, Soccer", "Bermuda", "Sod", "1 year", "Sun", "0", "This is a description of field 1")');
$db->exec('INSERT INTO fields (name, address, city, state, zip, multiple_sport_usage, sports_played, turfgrass_species_present, establishment_method, establishment_age, shade_or_sun, percent_shade, description) VALUES ("Field 2", "456 Main St", "Anytown", "NY", "12345", "Yes", "Football, Soccer", "Bermuda", "Sod", "1 year", "Sun", "0", "This is a description of field 2")');
$db->exec('INSERT INTO fields (name, address, city, state, zip, multiple_sport_usage, sports_played, turfgrass_species_present, establishment_method, establishment_age, shade_or_sun, percent_shade, description) VALUES ("Field 3", "789 Main St", "Anytown", "NY", "12345", "Yes", "Football, Soccer", "Bermuda", "Sod", "1 year", "Sun", "0", "This is a description of field 3")');

// Close the database connection
$db->close();