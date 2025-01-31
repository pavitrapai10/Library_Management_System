<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {

    header('Location: login.php'); // Redirect to login if session not set
        exit();
    }
    
    // Include database connection
    include 'db.php';
    
    // Fetch the logged-in user's details
    $username = $_SESSION['username'];
    $query = "SELECT role FROM borrowers WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Check if the user is an admin
    if ($user['role'] !== 'admin') {
        die('Access denied. You do not have permission to view this page.');
    }
    
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Library Management</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1> 
        <a href="admin.php?logout=true" class="logout-button">Logout</a>  
        <a href="manage_books.php" class="add-books-button">Add Books</a>
        <a href="user_logs.php" class="log-button">View User Logs</a>

    </header>
    

    

    <main id="admin-page">
        <h2>User Management</h2>
       
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a> |
                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a> 
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> Library Management System
    </footer>

 
    <script src="script.js"></script>
</body>
</html> 







