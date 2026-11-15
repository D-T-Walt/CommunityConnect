<?php
// Define the database server host
$host = "localhost";

// Define the database username
$username = "root";

// Define the database password (empty by default for local root)
$password = "";

// Define the target database name
$database = "community_connectdb";

// Create a new MySQLi connection instance
try {
    $conn = new mysqli($host, $username, $password, $database);
} catch (Exception $e) {
    // Handle the exception if the connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>