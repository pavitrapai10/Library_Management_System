<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';

$username = $_SESSION['username'];
$query = "SELECT role_id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$query = "SELECT * FROM borrowers";
$users_result = $conn->query($query);

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
            <a href="book_stock.php" class="book-stock-button">Book Stocks</a> <!-- New Button -->
            
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
                        <?php if ($user['role_id'] === 1): ?>



                            <td>
                            <div class="action-buttons">
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="edbutton edit-button">Edit</a>
                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="edbutton delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
    </div>
</td>




                        <?php else: ?>
                            <button class="edbutton disabled-button" disabled>Edit</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<footer>
    &copy; <?php echo date("Y"); ?> Library Management System
</footer>

<script src="script.js"></script>
</body>
</html>
