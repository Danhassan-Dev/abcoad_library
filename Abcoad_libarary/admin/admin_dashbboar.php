<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

// Get statistics
$stats = [
    'categories' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM categories"))['count'],
    'authors' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM authors"))['count'],
    'books' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM books"))['count'],
    'issued' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_books WHERE status = 'issued'"))['count'],
    'students' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'],
    'overdue' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()"))['count'],
    'pending_requests' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_requests WHERE status = 'pending'"))['count']
];

// Get recently issued books
$recent_issues = mysqli_query($conn, "
    SELECT ib.*, b.title, s.full_name, s.student_id 
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.status = 'issued'
    ORDER BY ib.issue_date DESC
    LIMIT 5
");

// Get overdue books
$overdue_books = mysqli_query($conn, "
    SELECT ib.*, b.title, s.full_name, s.student_id 
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.status = 'issued' AND ib.due_date < CURDATE()
    ORDER BY ib.due_date ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            max-width: 1400px;
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
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.red { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .stat-icon.teal { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header h2 {
            color: #333;
            font-size: 1.3em;
        }

        .btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-block;
            font-size: 0.9em;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .list-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-item:hover {
            background: #f8f9fa;
        }

        .item-info h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .item-info p {
            color: #666;
            font-size: 0.85em;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .quick-action:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .quick-action-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .quick-action-label {
            font-weight: 600;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">⚙️ Admin Panel</div>
        <div class="navbar-menu">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="book_requests.php">Book Requests</a>
            <a href="manage_categories.php">Categories</a>
            <a href="manage_authors.php">Authors</a>
            <a href="manage_books.php">Books</a>
            <a href="manage_students.php">Students</a>
            <a href="issue_book.php">Issue Book</a>
            <a href="admin_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo $_SESSION['full_name']; ?>!</h1>
            <p>Here's an overview of your library system</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📁</div>
                <div class="stat-number"><?php echo $stats['categories']; ?></div>
                <div class="stat-label">Categories</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">✍️</div>
                <div class="stat-number"><?php echo $stats['authors']; ?></div>
                <div class="stat-label">Authors</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">📚</div>
                <div class="stat-number"><?php echo $stats['books']; ?></div>
                <div class="stat-label">Total Books</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">📖</div>
                <div class="stat-number"><?php echo $stats['issued']; ?></div>
                <div class="stat-label">Issued Books</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon teal">👥</div>
                <div class="stat-number"><?php echo $stats['students']; ?></div>
                <div class="stat-label">Registered Students</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">⚠️</div>
                <div class="stat-number"><?php echo $stats['overdue']; ?></div>
                <div class="stat-label">Overdue Books</div>
            </div>
            
            <div class="stat-card" style="cursor: pointer;" onclick="window.location='book_requests.php'">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">📋</div>
                <div class="stat-number"><?php echo $stats['pending_requests']; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="book_requests.php" class="quick-action">
                <div class="quick-action-icon">📋</div>
                <div class="quick-action-label">Book Requests</div>
            </a>
            <a href="manage_categories.php" class="quick-action">
                <div class="quick-action-icon">📁</div>
                <div class="quick-action-label">Manage Categories</div>
            </a>
            <a href="manage_authors.php" class="quick-action">
                <div class="quick-action-icon">✍️</div>
                <div class="quick-action-label">Manage Authors</div>
            </a>
            <a href="manage_books.php" class="quick-action">
                <div class="quick-action-icon">📚</div>
                <div class="quick-action-label">Manage Books</div>
            </a>
            <a href="issue_book.php" class="quick-action">
                <div class="quick-action-icon">➕</div>
                <div class="quick-action-label">Issue Book</div>
            </a>
            <a href="return_book.php" class="quick-action">
                <div class="quick-action-icon">↩️</div>
                <div class="quick-action-label">Return Book</div>
            </a>
            <a href="manage_students.php" class="quick-action">
                <div class="quick-action-icon">👥</div>
                <div class="quick-action-label">View Students</div>
            </a>
        </div>

        <div class="content-grid">
            <div class="section">
                <div class="section-header">
                    <h2>Recent Issues</h2>
                    <a href="issued_books.php" class="btn">View All</a>
                </div>

                <?php if (mysqli_num_rows($recent_issues) > 0): ?>
                    <?php while ($issue = mysqli_fetch_assoc($recent_issues)): ?>
                        <div class="list-item">
                            <div class="item-info">
                                <h4><?php echo $issue['title']; ?></h4>
                                <p>Student: <?php echo $issue['full_name']; ?> (<?php echo $issue['student_id']; ?>)</p>
                                <p>Issued: <?php echo formatDate($issue['issue_date']); ?></p>
                            </div>
                            <span class="badge badge-info">Active</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">No recent issues</div>
                <?php endif; ?>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Overdue Books</h2>
                    <a href="overdue_books.php" class="btn">View All</a>
                </div>

                <?php if (mysqli_num_rows($overdue_books) > 0): ?>
                    <?php while ($overdue = mysqli_fetch_assoc($overdue_books)): ?>
                        <div class="list-item">
                            <div class="item-info">
                                <h4><?php echo $overdue['title']; ?></h4>
                                <p>Student: <?php echo $overdue['full_name']; ?> (<?php echo $overdue['student_id']; ?>)</p>
                                <p>Due: <?php echo formatDate($overdue['due_date']); ?> (<?php echo daysDifference(date('Y-m-d'), $overdue['due_date']); ?> days overdue)</p>
                            </div>
                            <span class="badge badge-danger">Overdue</span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">No overdue books 🎉</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>