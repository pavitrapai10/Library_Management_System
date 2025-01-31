<?php
include 'db.php';
// Fetch all borrow dates for dropdown
$dateQuery = "SELECT DISTINCT borrow_date FROM borrowed_books ORDER BY borrow_date DESC";
$dateResult = $conn->query($dateQuery);

// Filter results based on selected date
$selectedDate = isset($_POST['borrow_date']) ? $_POST['borrow_date'] : '';
$sql = "SELECT users.name AS user_name, books.title AS book_title, borrowed_books.borrow_date 
        FROM borrowed_books
        JOIN users ON borrowed_books.user_id = users.id
        JOIN books ON borrowed_books.book_id = books.id";
if ($selectedDate) {
    $sql .= " WHERE borrowed_books.borrow_date = '$selectedDate'";
}
$sql .= " ORDER BY borrowed_books.borrow_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e6f7e6;
        }
        .no-data {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Borrowed Books List</h1>
    
    <form method="POST" action="">
        <label for="borrow_date">Filter by Date:</label>
        <select name="borrow_date" id="borrow_date">
            <option value="">-- All Dates --</option>
            <?php
            if ($dateResult->num_rows > 0) {
                while ($row = $dateResult->fetch_assoc()) {
                    $date = $row['borrow_date'];
                    $selected = ($date === $selectedDate) ? "selected" : "";
                    echo "<option value='$date' $selected>$date</option>";
                }
            }
            ?>
        </select>
        <button type="submit">Filter</button>
    </form>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['book_title']) ?></td>
                        <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No borrowed books found for the selected date.</p>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
