<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';
$issued_books = null;
$student = null;

// Search student
if (isset($_POST['search_student'])) {
    $student_id = sanitize($_POST['student_id']);
    
    $query = "SELECT * FROM students WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // Get issued books for this student
        $issued_books = mysqli_query($conn, "
            SELECT ib.*, b.title, b.isbn, a.author_name, c.category_name 
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.id
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE ib.student_id = {$student['id']} AND ib.status = 'issued'
            ORDER BY ib.issue_date DESC
        ");
    } else {
        $error = "Student not found! Please check the Student ID.";
    }
}

// Return book
if (isset($_POST['return_book'])) {
    $issue_id = sanitize($_POST['issue_id']);
    $book_id = sanitize($_POST['book_id']);
    $return_date = date('Y-m-d');
    
    // Update issue record
    $update_query = "UPDATE issued_books SET status = 'returned', return_date = '$return_date' WHERE id = '$issue_id'";
    
    if (mysqli_query($conn, $update_query)) {
        // Update book availability
        mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = '$book_id'");
        $message = "Book returned successfully!";
        
        // Refresh issued books
        if ($student) {
            $issued_books = mysqli_query($conn, "
                SELECT ib.*, b.title, b.isbn, a.author_name, c.category_name 
                FROM issued_books ib
                JOIN books b ON ib.book_id = b.id
                JOIN authors a ON b.author_id = a.id
                JOIN categories c ON b.category_id = c.id
                WHERE ib.student_id = {$student['id']} AND ib.status = 'issued'
                ORDER BY ib.issue_date DESC
            ");
        }
    } else {
        $error = "Failed to return book!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book</title>
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

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        .search-section {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1em;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-weight: 600;
            font-size: 1.1em;
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

        .btn-return {
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9em;
        }

        .btn-return:hover {
            background: #45a049;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 12px;
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
            color: #666;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .search-section {
                flex-direction: column;
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
        <div class="navbar-brand">⚙️ Admin Panel</div>
        <div class="navbar-menu">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_categories.php">Categories</a>
            <a href="manage_authors.php">Authors</a>
            <a href="manage_books.php">Books</a>
            <a href="issue_book.php">Issue Book</a>
            <a href="return_book.php">Return Book</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>↩️ Return Book</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Search Student</h2>
            <form method="POST">
                <div class="search-section">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" placeholder="Enter Student ID (e.g., STU20241234)" required>
                    </div>
                    <button type="submit" name="search_student" class="btn">Search Student</button>
                </div>
            </form>
        </div>

        <?php if ($student): ?>
            <div class="card">
                <h2>Student Information</h2>
                <div class="student-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Student ID</span>
                            <span class="info-value"><?php echo $student['student_id']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo $student['full_name']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $student['email']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Issued Books</h2>
                <?php if ($issued_books && mysqli_num_rows($issued_books) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = mysqli_fetch_assoc($issued_books)): 
                                $is_overdue = strtotime($book['due_date']) < time();
                                $days_diff = daysDifference(date('Y-m-d'), $book['due_date']);
                            ?>
                            <tr>
                                <td><strong><?php echo $book['title']; ?></strong></td>
                                <td><?php echo $book['author_name']; ?></td>
                                <td><?php echo $book['isbn']; ?></td>
                                <td><?php echo formatDate($book['issue_date']); ?></td>
                                <td><?php echo formatDate($book['due_date']); ?></td>
                                <td>
                                    <?php if ($is_overdue): ?>
                                        <span class="badge badge-danger">Overdue (<?php echo $days_diff; ?> days)</span>
                                    <?php elseif ($days_diff <= 3): ?>
                                        <span class="badge badge-warning">Due Soon</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="issue_id" value="<?php echo $book['id']; ?>">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" name="return_book" class="btn-return" onclick="return confirm('Confirm book return?')">Return Book</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No Books Currently Issued</h3>
                        <p>This student has no books to return.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>