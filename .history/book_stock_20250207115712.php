<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit();
}

include 'db.php';

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $book_id = intval($_POST['book_id']);
    $new_total_copies = intval($_POST['total_copies']);

    // Fetch current available copies
    $fetchCopiesQuery = "SELECT available_copies FROM books WHERE id = ?";
    $stmt = $conn->prepare($fetchCopiesQuery);
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $stmt->bind_result($current_available_copies);
    $stmt->fetch();
    $stmt->close();

    // Update total copies and adjust available copies if necessary
    $new_available_copies = max(0, $new_total_copies - ($new_total_copies - $current_available_copies));

    $updateQuery = "UPDATE books SET total_copies = ?, available_copies = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('iii', $new_total_copies, $new_available_copies, $book_id);
    $stmt->execute();
    $stmt->close();

    header('Location: book_stock.php?message=Book stock updated successfully');
    exit();
}

// Fetch all books for display
$query = "SELECT id, title, total_copies, available_copies FROM books";
$result = $conn->query($query);
?>

