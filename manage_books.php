<?php
// Start the session and include database connection
session_start();
include 'db.php';

// Check if the user is logged in
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

// Fetch all books with author names
$books_query = "
    SELECT books.id, books.title, authors.name AS author, books.genre, books.isbn, books.published_date
    FROM books
    JOIN authors ON books.author_id = authors.id
    ORDER BY books.created_at DESC";
$books_result = $conn->query($books_query);

// Add a new book (only for admin users)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book']) && $loggedInUser['role'] === 'admin') {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $genre = trim($_POST['genre']);
    $isbn = trim($_POST['isbn']);
    $published_date = trim($_POST['published_date']);

    // Validate required fields
    if ($title && $author_name) {
        // Check if the author exists or create a new author
        $author_query = "SELECT id FROM authors WHERE name = ?";
        $stmt = $conn->prepare($author_query);
        $stmt->bind_param('s', $author_name);
        $stmt->execute();
        $author_result = $stmt->get_result();

        if ($author_result->num_rows > 0) {
            // Author exists, fetch the ID
            $author_row = $author_result->fetch_assoc();
            $author_id = $author_row['id'];
        } else {
            // Author does not exist, create a new author
            $insert_author_query = "INSERT INTO authors (name) VALUES (?)";
            $stmt = $conn->prepare($insert_author_query);
            $stmt->bind_param('s', $author_name);
            if ($stmt->execute()) {
                $author_id = $stmt->insert_id; // Get the new author's ID
            } else {
                echo "<p style='color:red;'>Error adding author: " . $stmt->error . "</p>";
                exit();
            }
        }

        // Insert the new book
        $query = "INSERT INTO books (title, author_id, genre, isbn, published_date) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sisss', $title, $author_id, $genre, $isbn, $published_date);
        if ($stmt->execute()) {
            header('Location: manage_books.php?message=Book added successfully');
            exit();
        } else {
            echo "<p style='color:red;'>Error adding book: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Title and Author are required!</p>";
    }
}
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
    </header>

    <main id="admin-page">
        <!-- Add Book Form (only visible to admins) -->
        <?php if ($loggedInUser['role'] === 'admin'): ?>
            <section class="form-container">
                <h2>Add a New Book</h2>
                <form method="POST" action="manage_books.php">
                    <input type="text" name="title" placeholder="Book Title" required>
                    <input type="text" name="author_name" placeholder="Author Name" required>
                    <input type="text" name="genre" placeholder="Genre">
                    <input type="text" name="isbn" placeholder="ISBN">
                    <input type="date" name="published_date" placeholder="Published Date">
                    <button type="submit" name="add_book">Add Book</button>
                </form>
            </section>
        <?php endif; ?>

        <!-- Books Table -->
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
                                <?php if ($loggedInUser['role'] === 'admin'): ?>
                                    <a href="edit_book.php?id=<?php echo $row['id']; ?>">Edit</a> |
                                    <a href="delete_book.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                                <?php else: ?>
                                    <span style="color: gray;">No Actions</span>
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
