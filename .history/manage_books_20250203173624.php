<?php
// Start the session and include database connection
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
$borrower_id = $loggedInUser['id'];

// Handle book borrowing
if (isset($_GET['borrow_book_id'])) {
    $book_id = intval($_GET['borrow_book_id']);

    // Check if the book is already borrowed by the user
    $checkQuery = "SELECT * FROM transactions WHERE borrower_id = ? AND book_id = ? AND return_date IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('ii', $borrower_id, $book_id);
    $stmt->execute();
    $borrowResult = $stmt->get_result();

    if ($borrowResult->num_rows == 0) {
        // Borrow the book
        $borrowQuery = "INSERT INTO transactions (borrower_id, book_id, borrow_date) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($borrowQuery);
        $stmt->bind_param('ii', $borrower_id, $book_id);
        $stmt->execute();
        header('Location: manage_books.php?message=Book borrowed successfully');
        exit();
    } else {
        header('Location: manage_books.php?message=Book already borrowed');
        exit();
    }
}

// Handle book return
if (isset($_GET['return_book_id'])) {
    $book_id = intval($_GET['return_book_id']);

    // Update the return date for the borrowed book
    $returnQuery = "UPDATE transactions SET return_date = NOW() WHERE borrower_id = ? AND book_id = ? AND return_date IS NULL";
    $stmt = $conn->prepare($returnQuery);
    $stmt->bind_param('ii', $borrower_id, $book_id);
    $stmt->execute();

    header('Location: manage_books.php?message=Book returned successfully');
    exit();
}

// Fetch all books with author names
$books_query = "
    SELECT books.id, books.title, authors.name AS author, books.genre, books.isbn, books.published_date
    FROM books
    JOIN authors ON books.author_id = authors.id
    ORDER BY books.created_at DESC";
$books_result = $conn->query($books_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Manage Books</h1>
        <a href="admin.php" class="logout-button">Back to Admin</a>
        <a href="my_borrowed_books.php" class="borrowbook-button">My Borrowed Books</a>

    </header>

    <main id="admin-page">
        <section>
            <h2>Books List</h2>
            <?php if (isset($_GET['message'])): ?>
                <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
            <?php endif; ?>
            <table>
            <thead>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Genre</th>
        <th>ISBN</th>
        <th>Published Date</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = $books_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['genre']); ?></td>
            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
            <td><?php echo htmlspecialchars($row['published_date']); ?></td>
            <td>
                <?php if ($loggedInUser['role'] !== 'Admin'): ?>
                    <?php
                    $checkBorrowedQuery = "SELECT * FROM transactions WHERE borrower_id = ? AND book_id = ? AND return_date IS NULL";
                    $stmt = $conn->prepare($checkBorrowedQuery);
                    $stmt->bind_param('ii', $borrower_id, $row['id']);
                    $stmt->execute();
                    $borrowedResult = $stmt->get_result();
                    ?>
                    <!-- Borrow Link -->
                    <a href="manage_books.php?borrow_book_id=<?php echo $row['id']; ?>"
                       <?php echo ($borrowedResult->num_rows > 0) ? 'style="color:gray; pointer-events:none;"' : ''; ?>>
                       Borrow
                    </a>

                    <!-- Return Link -->
                    |
                    <a href="manage_books.php?return_book_id=<?php echo $row['id']; ?>"
                       <?php echo ($borrowedResult->num_rows == 0) ? 'style="color:gray; pointer-events:none;"' : ''; ?>>
                       Return
                    </a>
                <?php else: ?>
                    <a href="edit_book.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="delete_book.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

                
            </table>
        </section>
    </main>
</body>
</html>
