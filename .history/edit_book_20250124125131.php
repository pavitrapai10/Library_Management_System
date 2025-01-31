<?php
session_start();
include 'db.php';  // Assuming db.php contains the database connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch book details based on the provided ID in the URL
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: manage_books.php');
    exit();
}

// Prepared statement for fetching the book details
$book_query = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($book_query);
$stmt->bind_param('i', $id);
$stmt->execute();
$book_result = $stmt->get_result();
$book = $book_result->fetch_assoc();

if (!$book) {
    header('Location: manage_books.php');
    exit();
}

// Fetch authors for the dropdown
$authors_query = "SELECT id, name FROM authors";
$authors_result = $conn->query($authors_query);
if (!$authors_result) {
    die("Error fetching authors: " . $conn->error);
}

// Update book details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
    $title = trim($_POST['title']);
    $author_id = (int)$_POST['author_id'];
    $genre = trim($_POST['genre']);
    $isbn = trim($_POST['isbn']);
    $published_date = trim($_POST['published_date']);

    // Update query using prepared statement
    $update_query = "UPDATE books SET 
                     title = ?, 
                     author_id = ?, 
                     genre = ?, 
                     isbn = ?, 
                     published_date = ? 
                     WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sisssi", $title, $author_id, $genre, $isbn, $published_date, $id);

    if ($stmt->execute()) {
        header('Location: manage_books.php');
        exit();
    } else {
        echo "<p style='color:red;'>Error updating book: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="style.css"> <!-- Assuming you have a stylesheet -->
</head>
<body>
    <header>
        <h1>Edit Book</h1>
    </header>

    <div class="form-container">
        <form method="POST" action="edit_book.php?id=<?php echo $id; ?>">
            <h2>Edit Book Details</h2>
            
            <label for="title">Book Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>

            <label for="author_id">Author:</label>
            <select name="author_id" required>
                <?php while ($author = $authors_result->fetch_assoc()): ?>
                    <option value="<?php echo $author['id']; ?>" 
                        <?php echo ($author['id'] == $book['author_id']) ? 'selected' : ''; ?>>
                        <?php echo $author['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="genre">Genre:</label>
            <input type="text" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>" required>

            <label for="isbn">ISBN:</label>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>

            <label for="published_date">Published Date:</label>
            <input type="date" name="published_date" value="<?php echo $book['published_date']; ?>" required>

            <button type="submit" name="update_book">Update Book</button>
        </form>
    </div>

</body>
</html>
