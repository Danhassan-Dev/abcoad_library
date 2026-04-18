<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (s.student_id LIKE '%$search%' OR s.full_name LIKE '%$search%' OR s.email LIKE '%$search%')";
}

// Get all students with issue counts
$students_query = "
    SELECT s.*, 
           COUNT(CASE WHEN ib.status = 'issued' THEN 1 END) as active_issues,
           COUNT(CASE WHEN ib.status = 'returned' THEN 1 END) as returned_books
    FROM students s
    LEFT JOIN issued_books ib ON s.id = ib.student_id
    $where
    GROUP BY s.id
    ORDER BY s.created_at DESC
";
$students = mysqli_query($conn, $students_query);

// Get student details for modal
if (isset($_GET['view'])) {
    $student_id = sanitize($_GET['view']);
    $student_details = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = '$student_id'"));
    
    // Get borrowing history
    $history = mysqli_query($conn, "
        SELECT ib.*, b.title, b.isbn, a.author_name 
        FROM issued_books ib
        JOIN books b ON ib.book_id = b.id
        JOIN authors a ON b.author_id = a.id
        WHERE ib.student_id = '$student_id'
        ORDER BY ib.issue_date DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            color: #333;
        }

        .search-box {
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
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

        .btn-view {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
        }

        .btn-view:hover {
            opacity: 0.8;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 12px;
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

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 900px;
            width: 90%;
            margin: 20px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-close {
            font-size: 1.5em;
            cursor: pointer;
            color: #999;
        }

        .student-profile {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-field {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .field-value {
            color: #333;
            font-weight: 600;
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
            }

            .search-box {
                max-width: 100%;
            }

            .student-profile {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">⚙️ Admin Panel</div>
        <div class="navbar-menu">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_categories.php">Categories</a>
            <a href="manage_authors.php">Authors</a>
            <a href="manage_books.php">Books</a>
            <a href="manage_students.php">Students</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>👥 Manage Students</h1>
            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by ID, name, or email..." value="<?php echo $search; ?>">
                </form>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo mysqli_num_rows($students); ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Active Issues</th>
                            <th>Total Borrowed</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        mysqli_data_seek($students, 0);
                        while ($student = mysqli_fetch_assoc($students)): 
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><strong><?php echo $student['student_id']; ?></strong></td>
                            <td><?php echo $student['full_name']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['phone'] ?: 'N/A'; ?></td>
                            <td>
                                <?php if ($student['active_issues'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $student['active_issues']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">0</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?php echo $student['returned_books']; ?></span></td>
                            <td><?php echo formatDate($student['created_at']); ?></td>
                            <td>
                                <a href="?view=<?php echo $student['id']; ?>" class="btn-view">View Details</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (isset($student_details)): ?>
    <div id="viewModal" class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Student Details</h2>
                <a href="manage_students.php" class="modal-close">&times;</a>
            </div>

            <div class="student-profile">
                <div class="profile-field">
                    <span class="field-label">Student ID</span>
                    <span class="field-value"><?php echo $student_details['student_id']; ?></span>
                </div>
                <div class="profile-field">
                    <span class="field-label">Full Name</span>
                    <span class="field-value"><?php echo $student_details['full_name']; ?></span>
                </div>
                <div class="profile-field">
                    <span class="field-label">Email</span>
                    <span class="field-value"><?php echo $student_details['email']; ?></span>
                </div>
                <div class="profile-field">
                    <span class="field-label">Phone</span>
                    <span class="field-value"><?php echo $student_details['phone'] ?: 'N/A'; ?></span>
                </div>
                <div class="profile-field" style="grid-column: 1 / -1;">
                    <span class="field-label">Address</span>
                    <span class="field-value"><?php echo $student_details['address'] ?: 'N/A'; ?></span>
                </div>
                <div class="profile-field">
                    <span class="field-label">Registered</span>
                    <span class="field-value"><?php echo formatDate($student_details['created_at']); ?></span>
                </div>
            </div>

            <h3 style="margin-bottom: 15px; color: #333;">Borrowing History</h3>
            <?php if (mysqli_num_rows($history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($record = mysqli_fetch_assoc($history)): ?>
                        <tr>
                            <td><strong><?php echo $record['title']; ?></strong></td>
                            <td><?php echo $record['author_name']; ?></td>
                            <td><?php echo formatDate($record['issue_date']); ?></td>
                            <td><?php echo formatDate($record['due_date']); ?></td>
                            <td><?php echo $record['return_date'] ? formatDate($record['return_date']) : 'Not returned'; ?></td>
                            <td>
                                <?php if ($record['status'] == 'returned'): ?>
                                    <span class="badge badge-success">Returned</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No borrowing history available.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>