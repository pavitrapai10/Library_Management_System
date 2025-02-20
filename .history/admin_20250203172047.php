<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login if session not set
    exit();
}

// Include database connection
include_once 'db.php';

// Fetch the logged-in user's details
$username = $_SESSION['username'];
$query = "SELECT first_name, last_name, role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Store user's full name for display
$user_full_name = $user['first_name'] . ' ' . $user['last_name'];

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

// Fetch all users if logged in as admin
if ($user['role'] === 'dmin') {
    $query = "SELECT id, username, first_name, last_name, email FROM borrowers";
    $users_result = $conn->query($query); // Execute the query and store the result
} else {
    $users_result = null; // For non-admin users, set it to null or redirect accordingly
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
        <h1>Welcome, <?php echo htmlspecialchars($user_full_name); ?>!</h1>
        <a href="admin.php?logout=true" class="logout-button">Logout</a>
        <a href="manage_books.php" class="add-books-button">Manage Books</a>
        
        <!-- Borrowed Books link visible only for Admin -->
        <?php if ($user['role'] === 'admin'): ?>
            <a href="borrowed_books.php" class="borrow-button">View Borrowed Books</a>
            <a href="user_logs.php" class="log-button">View User Logs</a>
        <?php endif; ?>
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
                <?php if ($users_result): ?>
                    <?php while ($row = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if ($row['username'] === $_SESSION['username']): ?>
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <?php elseif ($user['role'] === 'Admin'): ?>
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <?php else: ?>
                                    <span style="color: gray;">Edit</span>
                                <?php endif; ?>

                                <?php if ($user['role'] === 'Admin'): ?>
                                    | <a href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found or access restricted.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> Library Management System
    </footer>

    <script src="script.js"></script>
</body>
</html>
