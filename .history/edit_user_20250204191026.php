<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the logged-in user's details
$username = $_SESSION['username'];
$query = "SELECT id, role_id FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

if (!$loggedInUser) {
    die('User not found.');
}

// Get the user ID from the GET request
if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid user ID. Redirecting to Admin Page.'); window.location.href = 'admin.php';</script>";
    exit();
}

$userId = $_GET['id'];

// Fetch the user details based on the ID
$query = "SELECT * FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Database error: ' . $conn->error);
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die('User not found.');
}

// Handle the form submission to update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = htmlspecialchars(trim($_POST['username']));
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));

    // Check for duplicate username
    $checkQuery = "SELECT COUNT(*) FROM borrowers WHERE username = ? AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('si', $newUsername, $userId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<script>alert('Username already exists. Please choose a different one.'); window.history.back();</script>";
    } else {
        $updateQuery = "UPDATE borrowers SET username = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ssssi', $newUsername, $firstName, $lastName, $email, $userId);

        if ($updateStmt->execute()) {
            echo "<script>alert('User updated successfully.'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Error updating user.'); window.history.back();</script>";
        }

        $updateStmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Edit User</h1>
    <a href="admin.php?logout=true" class="logout-button">Logout</a>
</header>

<main>
    <form method="POST" class="form-container">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <button type="submit">Update User</button>
        <a href="admin.php" class="close-button">Close</a>
    </form>
</main>
</body>
</html>
