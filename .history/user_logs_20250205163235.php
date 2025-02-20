<?php
session_start(); // Start session
include 'db.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the role_id of the logged-in user
$loggedInUserId = $_SESSION['id'];
$query = "SELECT role_id FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user is an Admin (role_id = 1)
if ($user['role_id'] != 1) {
    header('Location: login.php');
    exit();
}

// Fetch user logs
$query = "SELECT * FROM user_logs ORDER BY timestamp DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>User Logs</h1>
    <a href="admin.php" class="backtoadmin-button">Back to Admin</a>
</header>
<main>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Action</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['action']); ?></td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
</body>
</html>
