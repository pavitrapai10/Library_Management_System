
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
