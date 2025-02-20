<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the logged-in user's details
$username = $_SESSION['username'];
$query = "SELECT role_id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

// Check if the logged-in user is an admin
if (!$loggedInUser || $loggedInUser['role_id'] !== 'Admin') {
    die('Access denied. Only admins can delete users.');
}

// Check if the user ID is provided in the GET request
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Ensure the user exists before attempting to delete
    $checkQuery = "SELECT * FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userToDelete = $result->fetch_assoc();

    if (!$userToDelete) {
        die('User not found.');
    }

    // Delete the user
    $deleteQuery = "DELETE FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    // Redirect back to the admin page with a success message
    header('Location: admin.php?message=User deleted successfully');
    exit();
} else {
    // Redirect back if no user ID is provided
    header('Location: admin.php');
    exit();
}
?>
