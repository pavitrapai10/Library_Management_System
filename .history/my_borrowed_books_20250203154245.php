<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the borrower ID of the logged-in user
$username = $_SESSION['username'];
$query = "SELECT id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$borrower_id = $user['id'];

// Fetch borrowed books
$borrowedBooksQuery = "
    SELECT books.title, authors.name AS author, transactions.borrow_date, transactions.return_date
    FROM transactions
    JOIN books ON transactions.book_id = books.id
    JOIN authors ON books.author_id = authors.id
    WHERE transactions.borrower_id = ? 
    ORDER BY transactions.borrow_date DESC";

$stmt = $conn->prepare($borrowedBooksQuery);
$stmt->bind_param('i', $borrower_id);
$stmt->execute();
$borrowedBooksResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>My Borrowed Books</h1>
        <a href="manage_books.php">Back to Books</a>
    </header>

    <main>
        <section>
            <h2>Borrowed Books</h2>
            <?php if (isset($_GET['message'])): ?>
                <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $borrowedBooksResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                            <td><?php echo $row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
