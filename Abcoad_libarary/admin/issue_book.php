<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';
$student = null;
$books = null;

// Search student
if (isset($_POST['search_student'])) {
    $student_id = sanitize($_POST['student_id']);
    
    $query = "SELECT * FROM students WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // Get available books
        $books = mysqli_query($conn, "
            SELECT b.*, a.author_name, c.category_name 
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.available_quantity > 0
            ORDER BY b.title
        ");
    } else {
        $error = "Student not found! Please check the Student ID.";
    }
}

// Issue book
if (isset($_POST['issue_book'])) {
    $student_id = sanitize($_POST['student_id']);
    $book_id = sanitize($_POST['book_id']);
    $days = sanitize($_POST['days']);
    
    // Get student DB id
    $student_query = mysqli_query($conn, "SELECT id FROM students WHERE student_id = '$student_id'");
    $student_data = mysqli_fetch_assoc($student_query);
    $student_db_id = $student_data['id'];
    
    // Check if book is available
    $book_check = mysqli_query($conn, "SELECT available_quantity FROM books WHERE id = '$book_id'");
    $book_data = mysqli_fetch_assoc($book_check);
    
    if ($book_data['available_quantity'] > 0) {
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$days days"));
        
        // Insert issue record
        $issue_query = "INSERT INTO issued_books (book_id, student_id, issue_date, due_date) 
                        VALUES ('$book_id', '$student_db_id', '$issue_date', '$due_date')";
        
        if (mysqli_query($conn, $issue_query)) {
            // Update book availability
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = '$book_id'");
            $message = "Book issued successfully! Due date: " . formatDate($due_date);
            
            // Reset for new issue
            $student = null;
            $books = null;
        } else {
            $error = "Failed to issue book!";
        }
    } else {
        $error = "This book is currently not available!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book</title>
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .book-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e1e8ed;
            transition: all 0.3s;
        }

        .book-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .book-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .book-detail {
            color: #666;
            font-size: 0.9em;
            margin: 8px 0;
        }

        .book-detail strong {
            color: #333;
        }

        .issue-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e1e8ed;
        }

        .issue-form input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .btn-issue {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-issue:hover {
            background: #45a049;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .search-section {
                flex-direction: column;
            }

            .books-grid {
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
            <a href="issue_book.php">Issue Book</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>➕ Issue Book to Student</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Step 1: Search Student</h2>
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
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo $student['phone'] ?: 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Step 2: Select Book to Issue</h2>
                <?php if (mysqli_num_rows($books) > 0): ?>
                    <div class="books-grid">
                        <?php while ($book = mysqli_fetch_assoc($books)): ?>
                            <div class="book-card">
                                <h3><?php echo $book['title']; ?></h3>
                                <div class="book-detail"><strong>Author:</strong> <?php echo $book['author_name']; ?></div>
                                <div class="book-detail"><strong>Category:</strong> <?php echo $book['category_name']; ?></div>
                                <div class="book-detail"><strong>ISBN:</strong> <?php echo $book['isbn']; ?></div>
                                <div class="book-detail">
                                    <strong>Available:</strong> 
                                    <span class="badge"><?php echo $book['available_quantity']; ?> copies</span>
                                </div>

                                <form method="POST" class="issue-form">
                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <input type="number" name="days" value="14" min="1" max="90" placeholder="Loan period (days)" required>
                                    <button type="submit" name="issue_book" class="btn-issue">Issue This Book</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">No books currently available for issuing.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>