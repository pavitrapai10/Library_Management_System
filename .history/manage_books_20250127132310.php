<?php
// Session check
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$query = "SELECT role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch books
$books_query = "SELECT * FROM books";
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
    </header>

    <main>
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
                </tr>
            </thead>
            <tbody>
                <?php while ($book = $books_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['id']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($book['published_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($user['role'] === 'admin'): ?>
            <!-- Admin-specific buttons -->
            <a href="add_book.php">Add New Book</a>
        <?php endif; ?>
    </main>
</body>
</html>
