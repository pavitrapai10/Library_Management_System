<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch the logged-in user's details
$username = $_SESSION['username'];
$query = "SELECT id, role FROM borrowers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

if (!$loggedInUser) {
    die('User not found.');
}

// Get the user ID from the GET request
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch the user details based on the ID
    $query = "SELECT * FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die('User not found.');
    }

    // Check if the logged-in user is trying to edit their own details or is an admin
    if ($loggedInUser['role'] !== 'admin' && $loggedInUser['id'] != $user['id']) {
        die('Access denied. You can only edit your own details.');
    }
} else {
    header('Location: admin.php');
    exit();
}

// Handle the form submission to update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $username = htmlspecialchars($_POST['username']);
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);

    // Update the user details
    $updateQuery = "UPDATE borrowers SET username = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ssssi', $username, $firstName, $lastName, $email, $userId);
    $updateStmt->execute();

    header('Location: admin.php'); // Redirect back to admin page after update
    exit();
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
        <a href="admin.php?logout=true" class="logo-button">Logout</a>
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
            <a href="admin.php" class="close-button">Close</a> <!-- Close button to go back without saving -->
        </form>
    </main>
</body>
</html>
