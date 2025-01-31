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
$query = "SELECT id, role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

if (!$loggedInUser) {
    die('User not found.');
}

// Get the user ID from the GET request
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch the user details based on the ID
    $query = "SELECT * FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die('User not found.');
    }

    // Check if the logged-in user is trying to edit their own details or is an admin
    if ($loggedInUser['role'] !== 'admin' && $loggedInUser['id'] != $user['id']) {
        die('Access denied. You can only edit your own details.');
    }
} else {
    header('Location: admin.php');
    exit();
}

// Handle the form submission to update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $newUsername = htmlspecialchars(trim($_POST['username']));
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));

    // Check for duplicate username (excluding current user)
    $checkQuery = "SELECT COUNT(*) FROM borrowers WHERE username = ? AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('si', $newUsername, $userId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<script>alert('Username already exists. Please choose a different one.'); window.history.back();</script>";
    } else {
        // Update the user details
        $updateQuery = "UPDATE borrowers SET username = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ssssi', $newUsername, $firstName, $lastName, $email, $userId);

        if ($updateStmt->execute()) {
            echo "<script>alert('User updated successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error updating user. Please try again.'); window.history.back();</script>";
        }

        $updateStmt->close();
    }

    $conn->close();
}
?>
