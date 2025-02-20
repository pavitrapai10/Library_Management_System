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
    $role_name = trim($_POST['role']);

    // Get the role ID based on role name
    $role_query = $conn->prepare("SELECT id FROM roles WHERE role_name = ?");
    $role_query->bind_param("s", $role_name);
    $role_query->execute();
    $role_query->store_result();
    $role_query->bind_result($id);
    $role_query->fetch();

    if (!$id) {
        echo "<script>
                alert('Invalid role selected.');
                window.history.back();
              </script>";
        exit;
    }

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

        // Correct query with role ID
        $query = "INSERT INTO borrowers (username, password, first_name, last_name, email, created_at, role_id) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("sssssi", $username, $hashedPassword, $first_name, $last_name, $email, $id); // Bind role ID as integer

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
            <div class="input-group">
                <input type="text" name="username" id="username" placeholder="Username" required>
                <span class="error-message" id="usernameError"></span>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="error-message" id="passwordError"></span>
            </div>

            <div class="input-group">
                <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
                <span class="error-message" id="confirmPasswordError"></span>
            </div>

            <div class="input-group">
                <input type="text" name="first_name" placeholder="First Name" required>
                <span class="error-message"></span>
            </div>

            <div class="input-group">
                <input type="text" name="last_name" placeholder="Last Name" required>
                <span class="error-message"></span>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
                <span class="error-message" id="emailError"></span>
            </div>

            <label for="role">Role</label>
            <select name="role" id="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Register</button>
        </form>

        <div id="globalErrorContainer" class="error-container">
            <?php if (!empty($error)): ?>
                <span class="error-message"><?php echo htmlspecialchars($error); ?></span>
            <?php endif; ?>
        </div>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>
