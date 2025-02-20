<?php
include 'db.php';


// Check if the user is logged in and is an admin
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the logged-in user's role
$username = $_SESSION['username'];
$query = "SELECT role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

// Restrict access to admins only
if (!$loggedInUser || $loggedInUser['role'] !== 'Admin') {
    die('Access denied. Only admins can delete books.');
}

// Fetch book ID from the URL
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    // Prepare query to delete the book
    $query = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        header('Location: manage_books.php?message=Book deleted successfully');
    } else {
        header('Location: manage_books.php?error=Error deleting book');
    }
} else {
    header('Location: manage_books.php?error=Invalid book ID');
}

exit();
?>
