<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    // Fetch the user details based on the ID
    $query = "SELECT * FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
} else {
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the form submission to update user details
    $username = $_POST['username'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];

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
        </form>
    </main>
</body>
</html>
