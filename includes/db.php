<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gatsinde_youth');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // For demo purposes, we'll use file-based storage
    // die("Connection failed: " . $conn->connect_error);
}

session_start();
?>
