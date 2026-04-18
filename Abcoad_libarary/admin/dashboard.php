<?php
// admin/dashboard.php
session_start();
require_once '../db.php';

// CHECK ADMIN ACCESS
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch statistics
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$total_borrowed = $pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status='borrowed'")->fetchColumn();
$total_returned = $pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status='returned'")->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - OLMS</title>
    <link rel="stylesheet" href="../assets/styles.css">

    <style>
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.15);
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #444;
        }

        .card p {
            font-size: 24px;
            color: #007bff;
            font-weight: bold;
        }

        nav a {
            margin: 6px;
        }
    </style>
</head>

<body>
<div class="container">

    <header>
        <h1>Admin Dashboard</h1>

        <nav>
            <a href="../index.php" class="btn">Home</a>
            <a href="add_book.php" class="btn">Add Book</a>
            <a href="manage_users.php" class="btn">Manage Users</a>
            <a href="../logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <h2>System Overview</h2>

    <div class="cards">

        <div class="card">
            <h3>Total Books</h3>
            <p><?php echo $total_books; ?></p>
        </div>

        <div class="card">
            <h3>Total Borrowed</h3>
            <p><?php echo $total_borrowed; ?></p>
        </div>

        <div class="card">
            <h3>Total Returned</h3>
            <p><?php echo $total_returned; ?></p>
        </div>

        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>

        <div class="card">
            <h3>Total Admins</h3>
            <p><?php echo $total_admins; ?></p>
        </div>

    </div>

    <br><br>

    <h2>Quick Actions</h2>

    <a href="add_book.php" class="btn">Add New Book</a>
    <a href="manage_users.php" class="btn">View All Users</a>
    <a href="../index.php" class="btn">Return to User Side</a>

</div>
</body>
</html>
