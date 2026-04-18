<?php
require_once 'config.php';

if (!isStudent()) {
    header('Location: student_dashboard.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Get statistics
$total_issued = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM issued_books WHERE student_id = $student_id AND status = 'issued'"))['count'];

$total_returned = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM issued_books WHERE student_id = $student_id AND status = 'returned'"))['count'];

$total_books = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM books WHERE available_quantity > 0"))['count'];

// Get currently issued books
$issued_books_query = "
    SELECT ib.*, b.title, b.isbn, a.author_name, c.category_name 
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE ib.student_id = $student_id AND ib.status = 'issued'
    ORDER BY ib.issue_date DESC
";
$issued_books = mysqli_query($conn, $issued_books_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5em;
            font-weight: bold;
        }

        .navbar-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .navbar-menu a:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .welcome-section h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
        }

        .stat-icon.blue { background: #e3f2fd; }
        .stat-icon.green { background: #e8f5e9; }
        .stat-icon.purple { background: #f3e5f5; }

        .stat-content h3 {
            color: #333;
            font-size: 2em;
            margin-bottom: 5px;
        }

        .stat-content p {
            color: #666;
            font-size: 0.9em;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">📚 Library System</div>
        <div class="navbar-menu">
            <a href="student_dashboard.php">Dashboard</a>
            <a href="browse_books.php">Browse Books</a>
            <a href="my_books.php">My Books</a>
            <a href="student_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo $_SESSION['full_name']; ?>!</h1>
            <p>Student ID: <strong><?php echo $_SESSION['student_id']; ?></strong></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-content">
                    <h3><?php echo $total_issued; ?></h3>
                    <p>Currently Borrowed</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">✓</div>
                <div class="stat-content">
                    <h3><?php echo $total_returned; ?></h3>
                    <p>Returned Books</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">📖</div>
                <div class="stat-content">
                    <h3><?php echo $total_books; ?></h3>
                    <p>Available Books</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Currently Borrowed Books</h2>
                <a href="browse_books.php" class="btn">Browse More Books</a>
            </div>

            <?php if (mysqli_num_rows($issued_books) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($issued_books)): 
                            $days_diff = daysDifference(date('Y-m-d'), $book['due_date']);
                            $is_overdue = strtotime($book['due_date']) < time();
                        ?>
                        <tr>
                            <td><strong><?php echo $book['title']; ?></strong></td>
                            <td><?php echo $book['author_name']; ?></td>
                            <td><?php echo $book['category_name']; ?></td>
                            <td><?php echo formatDate($book['issue_date']); ?></td>
                            <td><?php echo formatDate($book['due_date']); ?></td>
                            <td>
                                <?php if ($is_overdue): ?>
                                    <span class="badge badge-danger">Overdue (<?php echo $days_diff; ?> days)</span>
                                <?php elseif ($days_diff <= 3): ?>
                                    <span class="badge badge-warning">Due Soon (<?php echo $days_diff; ?> days)</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>You haven't borrowed any books yet.</p>
                    <a href="browse_books.php" class="btn" style="margin-top: 20px;">Browse Books</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>