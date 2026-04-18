<?php
require_once 'config.php';

if (!isStudent()) {
    header('Location: student_login.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';

// Cancel request
if (isset($_POST['cancel_request'])) {
    $request_id = sanitize($_POST['request_id']);
    $delete = "DELETE FROM book_requests WHERE id = '$request_id' AND student_id = $student_id AND status = 'pending'";
    if (mysqli_query($conn, $delete)) {
        $message = "Request cancelled successfully!";
    }
}

// get all request
$requests = mysqli_query($conn, "
    SELECT br.*, b.title, b.isbn, a.author_name, c.category_name 
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE br.student_id = $student_id
    ORDER BY br.request_date DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests</title>
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
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background: #d4edda;
            color: #155724;
        }

        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-cancel {
            padding: 6px 12px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .btn-cancel:hover {
            background: #c82333;
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

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
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
            <a href="my_requests.php">My Requests</a>
            <a href="student_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>📋 My Book Requests</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (mysqli_num_rows($requests) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Admin Response</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = mysqli_fetch_assoc($requests)): ?>
                        <tr>
                            <td><strong><?php echo $request['title']; ?></strong></td>
                            <td><?php echo $request['author_name']; ?></td>
                            <td><?php echo $request['category_name']; ?></td>
                            <td><?php echo formatDate($request['request_date']); ?></td>
                            <td>
                                <?php if ($request['status'] == 'pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php elseif ($request['status'] == 'approved'): ?>
                                    <span class="badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $request['admin_response'] ?: 'Waiting for review'; ?></td>
                            <td>
                                <?php if ($request['status'] == 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" name="cancel_request" class="btn-cancel" onclick="return confirm('Cancel this request?')">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Requests Yet</h3>
                    <p>You haven't requested any books yet.</p>
                    <a href="browse_books.php" class="btn">Browse Books</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>