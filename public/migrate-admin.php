<?php
/**
 * Migration script to add is_admin column to users table
 * and create a superadmin user
 */

// Open a connection to the SQLite database
$db = new SQLite3('turfgrass.db');

echo "Starting migration...\n\n";

// Check if is_admin column already exists
$result = $db->query("PRAGMA table_info(users)");
$columns = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $columns[] = $row['name'];
}

if (!in_array('is_admin', $columns)) {
    echo "Adding is_admin column to users table...\n";
    $db->exec('ALTER TABLE users ADD COLUMN is_admin INTEGER DEFAULT 0');
    echo "✓ is_admin column added\n\n";
} else {
    echo "✓ is_admin column already exists\n\n";
}

// Check if superadmin user exists
$admin_email = 'admin@tgmsuite.com';
$result = $db->query("SELECT * FROM users WHERE email = '$admin_email'");
$admin_exists = $result->fetchArray(SQLITE3_ASSOC);

if (!$admin_exists) {
    echo "Creating superadmin user...\n";
    $admin_user = 'Super Admin';
    $admin_password = "admin123";
    $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $db->exec("INSERT INTO users (name, email, password, is_admin) 
              VALUES ('$admin_user', '$admin_email', '$admin_password_hash', 1)");
    
    echo "✓ Superadmin user created\n";
    echo "  Email: admin@tgmsuite.com\n";
    echo "  Password: admin123\n\n";
    echo "⚠️  IMPORTANT: Please change the password after first login!\n";
} else {
    echo "✓ Superadmin user already exists\n";
    echo "  Email: admin@tgmsuite.com\n\n";
    
    // Update existing admin to be admin
    $db->exec("UPDATE users SET is_admin = 1 WHERE email = '$admin_email'");
}

// Close the database connection
$db->close();

echo "\nMigration complete!\n";
echo "You can now access the admin panel at /admin/submissions\n";
?>
