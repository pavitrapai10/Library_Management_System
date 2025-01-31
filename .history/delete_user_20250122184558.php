<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    // Delete the user from the borrowers table
    $deleteQuery = "DELETE FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    header('Location: admin.php'); // Redirect back to admin page after deletion
    exit();
} else {
    header('Location: admin.php');
    exit();
}
?>
