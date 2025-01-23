<?php

// connect to database
$db = new SQLite3('turfgrass.db');

// alter the table to add a column
$db->exec("ALTER TABLE photos ADD COLUMN associated_report_id INTEGER");

$db->exec("PRAGMA foreign_keys = OFF;"); 

// create new table
$db->exec("CREATE TABLE photos_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER,
    photo_url TEXT,
    photo_comments TEXT,
    associated_report_id INTEGER,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (associated_report_id) REFERENCES reports(id)
);");

// copy data from the old photos table to new one
$db->exec("INSERT INTO photos_new (id, report_id, photo_url, photo_comments) 
           SELECT id, report_id, photo_url, photo_comments FROM photos;");

// delete the old photos table
$db->exec("DROP TABLE photos;");

// rename the new photos table to photos
$db->exec("ALTER TABLE photos_new RENAME TO photos;");

$db->exec("PRAGMA foreign_keys = ON;");

//close the database
$db->close();
?>
