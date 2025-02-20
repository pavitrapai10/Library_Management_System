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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Stock Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Book Stock Management</h1>
    <a href="admin.php" class="backtoadmin-button">Back to Admin</a>
</header>

<main>
    <!-- Success Message -->
    <?php if (isset($_GET['message'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_GET['message']); ?></p>
    <?php endif; ?>

    <h2>Manage Book Stocks</h2>
    <table class="book-stock-table">
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Total Copies</th>
                <th>Available Copies</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_copies']); ?></td>
                    <td><?php echo htmlspecialchars($row['available_copies']); ?></td>
                    <td>
                        <div class="book-stock-actions">
                            <form method="POST" action="book_stock.php">
                                <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="total_copies" value="<?php echo $row['total_copies']; ?>" min="0" required>
                                <button type="submit" name="update_stock" class="update-stock-button">Update Stock</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<footer>
    &copy; <?php echo date("Y"); ?> Library Management System
</footer>
</body>
</html>
