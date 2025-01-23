<?php

$db = new SQLite3('turfgrass.db');


$db->exec('PRAGMA foreign_keys=ON');
$db->exec('ALTER TABLE reports ADD COLUMN associated_photo_id INTEGER');


$db->exec('
    UPDATE reports 
    SET associated_photo_id = (
        SELECT id 
        FROM photos 
        WHERE photos.report_id = reports.id 
        LIMIT 1
    )
');

$db->exec('
    ALTER TABLE photos 
    ADD COLUMN associated_report_id INTEGER
');

$db->exec('
    ALTER TABLE photos 
    ADD CONSTRAINT fk_associated_report 
    FOREIGN KEY (associated_report_id) REFERENCES reports(id)
');

$db->close();

?>