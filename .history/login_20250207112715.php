<?php
session_start();
include 'db.php'; // Ensure your database connection works

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Trim input to remove whitespace
    $password = trim($_POST['password']); // Raw password input

    // Query to check the username and fetch role_id
    $query = "SELECT * FROM borrowers WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Valid login
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $user['id'];  // Store user ID
            $_SESSION['role_id'] = $user['role_id']; // Store user role ID

            // Log the login event
            $logQuery = "INSERT INTO user_logs (username, action) VALUES (?, 'login')";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("s", $username);
            $logStmt->execute();

            // Redirect based on user role
            if ($user['role_id'] == 1) {
                header('Location: admin.php'); // Admin dashboard
            } else {
                header('Location: user_dashboard.php'); // Regular user dashboard
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
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
