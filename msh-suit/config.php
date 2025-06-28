<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'suit_store');
define('DB_USER', 'root');
define('DB_PASS', '');

// MySQLi Connection
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        die("MySQLi Connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8");
} catch(Exception $e) {
    die("MySQLi Connection failed: " . $e->getMessage());
}

// Backward compatibility - keeping $conn for any existing code that might use it
$conn = $mysqli;

// Site configuration
define('SITE_URL', 'http://localhost/suit/final suits/');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

// Ensure upload directories exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(UPLOAD_PATH . 'products/')) {
    mkdir(UPLOAD_PATH . 'products/', 0755, true);
}
?>