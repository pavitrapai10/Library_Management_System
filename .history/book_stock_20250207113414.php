<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit();
}

include 'db.php';
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
    <table>
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Total Copies</th>
                <th>Available Copies</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_copies']); ?></td>
                    <td><?php echo htmlspecialchars($row['available_copies']); ?></td>
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
