<?php
// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lms_system';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    throw new Exception("Database connection failed");
}

// Set charset to ensure proper encoding
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error setting charset: " . $conn->error);
    throw new Exception("Error setting charset");
}
?> 

