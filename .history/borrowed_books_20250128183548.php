<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch the borrowed books and their associated borrower details
$query = "
    SELECT
        b.id AS book_id,
        b.title AS book_title,
        br.first_name,
        br.last_name,
        br.username,
        bb.borrow_date,
        bb.return_date
    FROM
        borrowed_books bb
    JOIN
        borrowers br ON bb.borrower_id = br.id
    JOIN
        books b ON bb.book_id = b.id
    ORDER BY
        bb.borrow_date DESC;
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Borrowed Books Records</h1>
        <a href="admin.php" class="close-button">Go Back to Admin</a>
    </header>

    <main>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Borrower Name</th>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                            <td>" . htmlspecialchars($row['book_title']) . "</td>
                            <td>" . htmlspecialchars($row['borrow_date']) . "</td>
                            <td>" . ($row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned') . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</body>
</html>
