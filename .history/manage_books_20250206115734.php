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
$query = "SELECT id, role_id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();
$borrower_id = $loggedInUser['id'];

// Handle book borrowing
if (isset($_GET['borrow_book_id'])) {
    $book_id = intval($_GET['borrow_book_id']);

    // Check book stock
    $stockQuery = "SELECT available_copies FROM books WHERE id = ?";
    $stmt = $conn->prepare($stockQuery);
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $stmt->bind_result($available_copies);
    $stmt->fetch();
    $stmt->close();

    if ($available_copies > 0) {
        // Check if the book is already borrowed by the user
        $checkQuery = "SELECT * FROM transactions WHERE borrower_id = ? AND book_id = ? AND return_date IS NULL";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('ii', $borrower_id, $book_id);
        $stmt->execute();
        $borrowResult = $stmt->get_result();

        if ($borrowResult->num_rows == 0) {
            // Borrow the book and decrement stock
            $borrowQuery = "INSERT INTO transactions (borrower_id, book_id, borrow_date) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($borrowQuery);
            $stmt->bind_param('ii', $borrower_id, $book_id);
            $stmt->execute();

            $updateStockQuery = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?";
            $stmt = $conn->prepare($updateStockQuery);
            $stmt->bind_param('i', $book_id);
            $stmt->execute();

            header('Location: manage_books.php?message=Book borrowed successfully');
            exit();
        } else {
            header('Location: manage_books.php?message=Book already borrowed');
            exit();
        }
    } else {
        header('Location: manage_books.php?message=No copies available');
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

    // Increment available copies
    $updateStockQuery = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
    $stmt = $conn->prepare($updateStockQuery);
    $stmt->bind_param('i', $book_id);
    $stmt->execute();

    header('Location: manage_books.php?message=Book returned successfully');
    exit();
}

// Add a new book (only for admin users)
$query = "INSERT INTO books (title, author_id, genre, isbn, published_date, total_copies, available_copies) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('sisssii', $title, $author_id, $genre, $isbn, $published_date, $total_copies, $total_copies);
$stmt->execute();


    if ($title && $author_name && $total_copies > 0) {
        // Check if the author exists or create a new author
        $author_query = "SELECT id FROM authors WHERE name = ?";
        $stmt = $conn->prepare($author_query);
        $stmt->bind_param('s', $author_name);
        $stmt->execute();
        $author_result = $stmt->get_result();

        if ($author_result->num_rows > 0) {
            $author_row = $author_result->fetch_assoc();
            $author_id = $author_row['id'];
        } else {
            $insert_author_query = "INSERT INTO authors (name) VALUES (?)";
            $stmt = $conn->prepare($insert_author_query);
            $stmt->bind_param('s', $author_name);
            $stmt->execute();
            $author_id = $stmt->insert_id;
        }

        $query = "INSERT INTO books (title, author_id, genre, isbn, published_date, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sisssii', $title, $author_id, $genre, $isbn, $published_date, $total_copies, $total_copies);
        $stmt->execute();
        header('Location: manage_books.php?message=Book added successfully');
        exit();
    } else {
        echo "<p style='color:red;'>Title, Author, and Total Copies are required!</p>";
    }
}

// Fetch all books with author names
$books_query = "
    SELECT books.id, books.title, authors.name AS author, books.genre, books.isbn, books.published_date, books.available_copies
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
    <?php if ($loggedInUser['role_id'] !== 1): ?>
        <a href="my_borrowed_books.php" class="borrowbook-button">My Borrowed Books</a>
    <?php endif; ?>
</header>

<main id="admin-page">
    <?php if ($loggedInUser['role_id'] === 1): ?>
        <section class="form-container">
            <h2>Add a New Book</h2>
            <form method="POST" action="manage_books.php">
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author_name" placeholder="Author Name" required>
                <input type="text" name="genre" placeholder="Genre">
                <input type="text" name="isbn" placeholder="ISBN">
                <input type="date" name="published_date" placeholder="Published Date">
                <input type="number" name="total_copies" placeholder="Total Copies" min="1" required>
                <button type="submit" name="add_book">Add Book</button>
            </form>
        </section>
    <?php endif; ?>

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
                    <th>Available Copies</th>
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
                        <td><?php echo $row['available_copies']; ?></td>
                        <td>
                            <?php if ($loggedInUser['role_id'] !== 1): ?>
                                <?php
                                $checkBorrowedQuery = "SELECT * FROM transactions WHERE borrower_id = ? AND book_id = ? AND return_date IS NULL";
                                $stmt = $conn->prepare($checkBorrowedQuery);
                                $stmt->bind_param('ii', $borrower_id, $row['id']);
                                $stmt->execute();
                                $borrowedResult = $stmt->get_result();
                                ?>
                                <button class="edbutton borrow-button"
                                    <?php echo ($borrowedResult->num_rows > 0 || $row['available_copies'] == 0) ? 'disabled' : ''; ?>
                                    onclick="window.location.href='manage_books.php?borrow_book_id=<?php echo $row['id']; ?>'">
                                    Borrow
                                </button>

                                <button class="edbutton return-button"
                                    <?php echo ($borrowedResult->num_rows == 0) ? 'disabled' : ''; ?>
                                    onclick="window.location.href='manage_books.php?return_book_id=<?php echo $row['id']; ?>'">
                                    Return
                                </button>
                            <?php else: ?>
                                <button class="edbutton edit-button"
                                    onclick="window.location.href='edit_book.php?id=<?php echo $row['id']; ?>'">
                                    Edit
                                </button>
                                <button class="edbutton delete-button"
                                    onclick="if(confirm('Are you sure you want to delete this book?')) window.location.href='delete_book.php?id=<?php echo $row['id']; ?>';">
                                    Delete
                                </button>
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
