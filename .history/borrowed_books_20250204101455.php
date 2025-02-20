<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Borrowed Books List</h1>
        <a href="admin.php?logout=true" class="logout-button">Logout</a>
        
    </header>

    <main>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Borrower Name</th>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch borrowed book details
                $query = "
                    SELECT 
                        borrowers.first_name, 
                        borrowers.last_name, 
                        books.title, 
                        transactions.borrow_date, 
                        transactions.return_date
                    FROM 
                        transactions
                    JOIN borrowers ON transactions.borrower_id = borrowers.id
                    JOIN books ON transactions.book_id = books.id
                    ORDER BY transactions.borrow_date DESC
                ";

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['borrow_date']) . "</td>";
                        echo "<td>" . ($row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No borrowed books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</body>
</html>
