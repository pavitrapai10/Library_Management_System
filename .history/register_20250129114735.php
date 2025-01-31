<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Use password_hash for secure password storage
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO borrowers (username, password, first_name, last_name, email, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssss', $username, $hashed_password, $first_name, $last_name, $email);

        if ($stmt->execute()) {
            header('Location: login.php');
            exit();
        } else {
            $error = "Failed to register user! Please ensure unique username and email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">Register</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>
