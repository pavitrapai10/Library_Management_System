<!-- <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";  // The server where MySQL is running
$user = "root";       // Default username for phpMyAdmin
$password = "";       // No password for MySQL
$dbname = "library_management"; // The name of your database

// Create a connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}
?> -->


<?php
$servername = "localhost";
$username = "root";
$password = ""; // Empty if there's no password set
$dbname = "library_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and suppress any errors
if ($conn->connect_error) {
    // You can log the error instead of displaying it, e.g., error_log($conn->connect_error);
    die();  // Do not display any error message, but stop further execution
}
?>
