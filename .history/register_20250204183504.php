<?php
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role_name = trim(&_POST['role']);



    // Server-side email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
        $error = "Please enter a valid Gmail address (e.g., example@gmail.com).";
    }
    // Server-side password validation
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/\d/', $password) || 
        !preg_match('/[@$!%*?&]/', $password)) {
        $error = "Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    }

    if (empty($error)) {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO borrowers (username, password, first_name, last_name, email, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("sssss", $username, $hashedPassword, $first_name, $last_name, $email);

            try {
                $stmt->execute();
                header('Location: login.php');
                exit();
            } catch (mysqli_sql_exception $e) {
                // Handle duplicate entry errors for both username and email
                if (strpos($e->getMessage(), "for key 'borrowers.username'") !== false) {
                    $error = "Username already exists. Please choose a different one.";
                } elseif (strpos($e->getMessage(), "for key 'borrowers.email'") !== false) {
                    $error = "Email already exists. Please use a different one.";
                } else {
                    $error = "Failed to register user. Please try again.";
                }
            }
        } else {
            $error = "Failed to prepare the statement. Please contact support.";
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
        <form id="registerForm" method="POST" action="">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <label for="role">Role</label>
            <select name="role" id="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
    </select>
            <button type="submit">Register</button>
        </form>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>
