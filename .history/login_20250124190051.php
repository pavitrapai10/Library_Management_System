<?php
session_start();
include 'db.php'; // Ensure your database connection works

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Trim input to remove whitespace
    $password = md5(trim($_POST['password'])); // Hash the password with MD5

    // Query to check username and password
    $query = "SELECT * FROM borrowers WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Valid login
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = ($user['username'] === 'admin'); // Set admin flag if username is 'admin'

        // Log the login event
        $logQuery = "INSERT INTO user_logs (username, action) VALUES (?, 'login')";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("s", $username);
        $logStmt->execute();

        // Redirect based on admin or regular user
        if ($_SESSION['is_admin']) {
            header('Location: admin.php');
        } else {
            header('Location: user_dashboard.php'); // Replace with a regular user dashboard
        }
        exit();
    } else {
        // Invalid credentials
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <!-- Display error if set -->
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>

