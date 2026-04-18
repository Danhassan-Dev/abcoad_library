<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $isbn = trim($_POST["isbn"]);

    if ($title == "") {
        $message = "Title is required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, availability) VALUES (?, ?, ?, 1)");
        $stmt->execute([$title, $author, $isbn]);
        $message = "Book added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add Book - Admin</title>
<link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
<div class="container">
    <h2>Add New Book</h2>

    <?php if ($message): ?>
    <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-row">
            <input type="text" name="title" placeholder="Book Title" required>
        </div>

        <div class="form-row">
            <input type="text" name="author" placeholder="Author Name">
        </div>

        <div class="form-row">
            <input type="text" name="isbn" placeholder="ISBN">
        </div>

        <button class="btn" type="submit">Add Book</button>
    </form>

    <br>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>

</div>
</body>
</html>

            <a href="dashboard.php">Dashboard</a>
            <a href="add_book.php">Add Book</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <div class="cards">
        <div class="card">
            <h3>Total Books</h3>
            <p><?php echo $total_books; ?></p>
        </div>
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="card">
            <h3>Total Admins</h3>
            <p><?php echo $total_admins; ?></p>
        </div>
        <div class="card">
            <h3>Books Borrowed</h3>
            <p><?php echo $total_borrowed; ?></p>
        </div>
        <div class="card">
            <h3>Books Returned</h3>
            <p><?php echo $total_returned; ?></p>
        </div>
    </div>