<?php
include 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrower_id = $_POST['borrower_id'];
    $book_id = $_POST['book_id'];

    $conn->begin_transaction();

    try {
        // Check book availability
        $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->bind_result($available_copies);
        $stmt->fetch();
        $stmt->close();

        if ($available_copies > 0) {
            // Insert borrow transaction
            $stmt = $conn->prepare("INSERT INTO transactions (borrower_id, book_id, borrow_date) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $borrower_id, $book_id);
            $stmt->execute();
            $stmt->close();

            // Decrease book copies
            $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            echo "Book borrowed successfully!";
        } else {
            $conn->rollback();
            echo "Book is out of stock!";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error borrowing book: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book</title>
</head>
<body>
    <h2>Borrow a Book</h2>
    <form method="POST" action="borrow_book.php">
        <label for="borrower_id">Borrower ID:</label>
        <input type="number" name="borrower_id" id="borrower_id" required>

        <label for="book_id">Book ID:</label>
        <input type="number" name="book_id" id="book_id" required>

        <button type="submit">Borrow Book</button>
    </form>
</body>
</html>
