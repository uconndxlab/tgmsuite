<?php
// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

// Step 2: Get a list of all tables in the database
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
while($table = $tables->fetchArray(SQLITE3_ASSOC)) {
    $db->exec('DELETE FROM ' . $table['name']);
    echo "Table " . $table['name'] . " has been emptied.<br>";
}

// Step 3: Generate a SQL script to drop all the tables
while ($table = $tables->fetchArray()) {
    $db->exec('DROP TABLE IF EXISTS ' . $table['name']);
    echo "Table " . $table['name'] . " has been dropped.<br>";
}

// Step 4: Close the database connection
$db->close();

echo "All tables have been dropped.";
