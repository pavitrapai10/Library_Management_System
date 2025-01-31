<?php
include 'db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $query = "DELETE FROM books WHERE id = $id";
    $conn->query($query);
}
header('Location: manage_books.php');
exit();
?>
