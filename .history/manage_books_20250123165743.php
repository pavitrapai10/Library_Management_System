<?php
// Start the session and include database connection
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch all books with author names
$books_query = "
    SELECT books.id, books.title, authors.name AS author, books.genre, books.isbn, books.published_date
    FROM books
    JOIN authors ON books.author_id = authors.id
    ORDER BY books.created_at DESC";
$books_result = $conn->query($books_query);

// Add a new book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $genre = trim($_POST['genre']);
    $isbn = trim($_POST['isbn']);
    $published_date = trim($_POST['published_date']);

    // Validate required fields
    if ($title && $author_name) {
        // Check if the author exists or create a new author
        $author_query = "SELECT id FROM authors WHERE name = '$author_name'";
        $author_result = $conn->query($author_query);

        if ($author_result->num_rows > 0) {
            // Author exists, fetch the ID
            $author_row = $author_result->fetch_assoc();
            $author_id = $author_row['id'];
        } else {
            // Author does not exist, create a new author
            $insert_author_query = "INSERT INTO authors (name) VALUES ('$author_name')";
            if ($conn->query($insert_author_query)) {
                $author_id = $conn->insert_id; // Get the new author's ID
            } else {
                echo "<p style='color:red;'>Error adding author: " . $conn->error . "</p>";
                exit();
            }
        }

        // Insert the new book
        $query = "INSERT INTO books (title, author_id, genre, isbn, published_date) 
                  VALUES ('$title', $author_id, '$genre', '$isbn', '$published_date')";
        if ($conn->query($query)) {
            header('Location: manage_books.php');
            exit();
        } else {
            echo "<p style='color:red;'>Error adding book: " . $conn->error . "</p>";
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
        <!-- Add Book Form -->
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

        <!-- Books Table -->
        <section>
            <h2>Books List</h2>
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
                                <a href="edit_book.php?id=<?php echo $row['id']; ?>">Edit</a> |
                                <a href="delete_book.php?id=<?php echo $row['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
