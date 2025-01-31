<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Check username uniqueness
    $check_username = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();
    
    if ($check_username->num_rows > 0) {
        echo "<script>
                alert('Username already exists!');
                window.history.back();
              </script>";
        exit;
    }

 // Check if passwords match
 if ($password !== $confirm_password) {
     echo "<script>
             alert('Passwords do not match. Please try again.');
             window.history.back();
           </script>";
     exit;
 }


     // Validate email format
     if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
         echo "<script>
                 alert('Invalid email format! Must be @gmail.com');
                 window.history.back();
               </script>";
         exit;
     }

 $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Encrypt password
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
