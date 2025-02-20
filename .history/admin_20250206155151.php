<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if session not set
    exit();
}

// Include database connection
include 'db.php';

// Fetch the logged-in user's details
$username = $_SESSION['username'];
$query = "SELECT role_id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch all users from the database (for the user table)
$query = "SELECT * FROM borrowers";
$users_result = $conn->query($query);

// Fetch all books for stock management
$books_query = "SELECT id, title, total_copies, available_copies FROM books";
$books_result = $conn->query($books_query);

// Handle stock updates (restricted to admins)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    if ($user['role_id'] !== 1) {
        header('Location: admin.php?error=Unauthorized access'); // Redirect if unauthorized
        exit();
    }

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

    header('Location: admin.php?message=Book stock updated successfully');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    $query = "INSERT INTO user_logs (username, action) VALUES (?, 'logout')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Library Management</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<header>
    <div class="header-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="left-buttons">
            <a href="manage_books.php" class="add-books-button">Manage Books</a>

            <?php if ($user['role_id'] === 1): ?>
                <a href="borrowed_books.php" class="borrow-button">View Borrowed Books</a>
                <a href="user_logs.php" class="log-button">View User Logs</a>
            <?php endif; ?>
        </div>
        <div class="right-buttons">
            <a href="admin.php?logout=true" class="logout-button">Logout</a>
        </div>
    </div>
</header>

<main id="admin-page">
    <h2>User Management</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php if ($row['username'] === $_SESSION['username'] && $user['role_id'] !== 1): ?>
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="edbutton self-edit-button">Edit</a>
                        <?php endif; ?>

                        <?php if ($user['role_id'] === 1): ?>
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="edbutton edit-button">Edit</a>
                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="edbutton delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php endif; ?>

                        <?php if ($row['username'] !== $_SESSION['username'] && $user['role_id'] !== 1): ?>
                            <button class="edbutton disabled-button" disabled>Edit</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Admin Book Stock Management -->
    <?php if ($user['role_id'] === 1): ?>
        <h2>Manage Book Stock</h2>
        <table>
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
                <?php while ($book = $books_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['id']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['total_copies']); ?></td>
                        <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                        <td>
                            <form method="POST" action="admin.php">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <input type="number" name="total_copies" value="<?php echo $book['total_copies']; ?>" min="0" required>
                                <button type="submit" name="update_stock">Update Stock</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<footer>
    &copy; <?php echo date("Y"); ?> Library Management System
</footer>

<script src="script.js"></script>
</body>
</html>
