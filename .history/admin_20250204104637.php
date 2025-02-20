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
$query = "SELECT role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch all users from the database (for the user table)
$query = "SELECT * FROM borrowers";
$users_result = $conn->query($query);

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

            <?php if ($user['role'] === 'Admin'): ?>
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
        <?php if ($row['username'] === $_SESSION['username']): ?>
            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="edbutton self-edit-button">Edit</a>
        <?php elseif ($user['role'] === 'Admin'): ?>
            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="edbutton edit-button">Edit</a>
            <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="edbutton delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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
