<?php
require_once 'config.php';

if (!isStudent()) {
    header('Location: student_login.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Get currently borrowed books
$active_books = mysqli_query($conn, "
    SELECT ib.*, b.title, b.isbn, a.author_name, c.category_name 
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE ib.student_id = $student_id AND ib.status = 'issued'
    ORDER BY ib.issue_date DESC
");

// Get borrowing history
$history = mysqli_query($conn, "
    SELECT ib.*, b.title, b.isbn, a.author_name, c.category_name 
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE ib.student_id = $student_id AND ib.status = 'returned'
    ORDER BY ib.return_date DESC
");

// Get statistics
$total_borrowed = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM issued_books WHERE student_id = $student_id"))['count'];

$total_returned = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM issued_books WHERE student_id = $student_id AND status = 'returned'"))['count'];

$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM issued_books WHERE student_id = $student_id AND status = 'issued' AND due_date < CURDATE()"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books</title>
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

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8em;
        }

        .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-icon.red { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 15px;
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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 15px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 24px;
            background: #f8f9fa;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 0.9em;
            }

            .table th,
            .table td {
                padding: 10px;
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
        <div class="page-header">
            <h1>📖 My Books</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-number"><?php echo mysqli_num_rows($active_books); ?></div>
                <div class="stat-label">Currently Borrowed</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">✓</div>
                <div class="stat-number"><?php echo $total_returned; ?></div>
                <div class="stat-label">Books Returned</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">⚠️</div>
                <div class="stat-number"><?php echo $overdue_count; ?></div>
                <div class="stat-label">Overdue Books</div>
            </div>
        </div>

        <div class="card">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('active')">Active Loans</button>
                <button class="tab" onclick="switchTab('history')">History</button>
            </div>

            <div id="active" class="tab-content active">
                <h2>Currently Borrowed Books</h2>
                <?php if (mysqli_num_rows($active_books) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>ISBN</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($active_books, 0);
                            while ($book = mysqli_fetch_assoc($active_books)): 
                                $is_overdue = strtotime($book['due_date']) < time();
                                $days_diff = daysDifference(date('Y-m-d'), $book['due_date']);
                            ?>
                            <tr>
                                <td><strong><?php echo $book['title']; ?></strong></td>
                                <td><?php echo $book['author_name']; ?></td>
                                <td><?php echo $book['category_name']; ?></td>
                                <td><?php echo $book['isbn']; ?></td>
                                <td><?php echo formatDate($book['issue_date']); ?></td>
                                <td><?php echo formatDate($book['due_date']); ?></td>
                                <td>
                                    <?php if ($is_overdue): ?>
                                        <span class="badge badge-danger">Overdue (<?php echo abs($days_diff); ?> days)</span>
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
                        <h3>No Active Loans</h3>
                        <p>You don't have any books borrowed at the moment.</p>
                        <a href="browse_books.php" class="btn">Browse Books</a>
                    </div>
                <?php endif; ?>
            </div>

            <div id="history" class="tab-content">
                <h2>Borrowing History</h2>
                <?php if (mysqli_num_rows($history) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = mysqli_fetch_assoc($history)): 
                                $returned_late = strtotime($book['return_date']) > strtotime($book['due_date']);
                            ?>
                            <tr>
                                <td><strong><?php echo $book['title']; ?></strong></td>
                                <td><?php echo $book['author_name']; ?></td>
                                <td><?php echo $book['category_name']; ?></td>
                                <td><?php echo formatDate($book['issue_date']); ?></td>
                                <td><?php echo formatDate($book['due_date']); ?></td>
                                <td><?php echo formatDate($book['return_date']); ?></td>
                                <td>
                                    <?php if ($returned_late): ?>
                                        <span class="badge badge-warning">Returned Late</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Returned On Time</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No History Yet</h3>
                        <p>You haven't returned any books yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Remove active class from all tabs and content
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
        }
    </script>
</body>
</html>