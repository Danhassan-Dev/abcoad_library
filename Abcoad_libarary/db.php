<?php
// db.php - MySQLi database connection

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'abcoad_library';

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Optional: enable strict error reporting (useful for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>