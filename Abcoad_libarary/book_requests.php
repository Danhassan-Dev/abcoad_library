<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Approve request and issue book
if (isset($_POST['approve_request'])) {
    $request_id = sanitize($_POST['request_id']);
    $book_id = sanitize($_POST['book_id']);
    $student_id = sanitize($_POST['student_id']);
    $days = 14; // Default loan period
    
    // Check if book is available
    $book_check = mysqli_query($conn, "SELECT available_quantity FROM books WHERE id = '$book_id'");
    $book_data = mysqli_fetch_assoc($book_check);
    
    if ($book_data['available_quantity'] > 0) {
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$days days"));
        
        // Issue the book
        $issue_query = "INSERT INTO issued_books (book_id, student_id, issue_date, due_date) 
                        VALUES ('$book_id', '$student_id', '$issue_date', '$due_date')";
        
        if (mysqli_query($conn, $issue_query)) {
            // Update book availability
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = '$book_id'");
            
            // Update request status
            mysqli_query($conn, "UPDATE book_requests SET status = 'approved', admin_response = 'Book issued successfully' WHERE id = '$request_id'");
            
            $message = "Request approved and book issued successfully!";
        } else {
            $error = "Failed to issue book!";
        }
    } else {
        $error = "Book is currently not available!";
    }
}

// Reject request
if (isset($_POST['reject_request'])) {
    $request_id = sanitize($_POST['request_id']);
    $reason = sanitize($_POST['reason']);
    
    $query = "UPDATE book_requests SET status = 'rejected', admin_response = '$reason' WHERE id = '$request_id'";
    
    if (mysqli_query($conn, $query)) {
        $message = "Request rejected successfully!";
    } else {
        $error = "Failed to reject request!";
    }
}

// Get all pending requests
$pending_requests = mysqli_query($conn, "
    SELECT br.*, b.title, b.isbn, b.available_quantity, a.author_name, c.category_name, 
           s.full_name, s.student_id as student_number, s.email
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    JOIN students s ON br.student_id = s.id
    WHERE br.status = 'pending'
    ORDER BY br.request_date ASC
");

// Get processed requests
$processed_requests = mysqli_query($conn, "
    SELECT br.*, b.title, s.full_name, s.student_id as student_number
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    JOIN students s ON br.student_id = s.id
    WHERE br.status != 'pending'
    ORDER BY br.request_date DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Requests</title>
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

        .request-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #e1e8ed;
        }

        .request-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .request-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .request-info p {
            color: #666;
            margin: 5px 0;
            font-size: 0.95em;
        }

        .request-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #e1e8ed;
        }

        .btn-approve {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-reject:hover {
            background: #c82333;
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

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
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

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
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
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-close {
            font-size: 1.5em;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            resize: vertical;
            min-height: 100px;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .request-header {
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
            <a href="book_requests.php">Book Requests</a>
            <a href="manage_books.php">Books</a>
            <a href="issue_book.php">Issue Book</a>
            <a href="return_book.php">Return Book</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>📋 Book Requests Management</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Pending Requests (<?php echo mysqli_num_rows($pending_requests); ?>)</h2>
            
            <?php if (mysqli_num_rows($pending_requests) > 0): ?>
                <?php while ($request = mysqli_fetch_assoc($pending_requests)): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-info">
                                <h3><?php echo $request['title']; ?></h3>
                                <p><strong>Author:</strong> <?php echo $request['author_name']; ?></p>
                                <p><strong>Category:</strong> <?php echo $request['category_name']; ?></p>
                                <p><strong>ISBN:</strong> <?php echo $request['isbn']; ?></p>
                            </div>
                            
                            <div class="request-info">
                                <h3><?php echo $request['full_name']; ?></h3>
                                <p><strong>Student ID:</strong> <?php echo $request['student_number']; ?></p>
                                <p><strong>Email:</strong> <?php echo $request['email']; ?></p>
                            </div>
                            
                            <div class="request-info">
                                <p><strong>Request Date:</strong></p>
                                <p><?php echo formatDate($request['request_date']); ?></p>
                                <p style="margin-top: 10px;"><strong>Available:</strong></p>
                                <?php if ($request['available_quantity'] > 0): ?>
                                    <span class="badge badge-success"><?php echo $request['available_quantity']; ?> copies</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Out of stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="request-actions">
                            <?php if ($request['available_quantity'] > 0): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="book_id" value="<?php echo $request['book_id']; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $request['student_id']; ?>">
                                    <button type="submit" name="approve_request" class="btn-approve" onclick="return confirm('Approve this request and issue the book?')">Approve & Issue</button>
                                </form>
                            <?php endif; ?>
                            
                            <button class="btn-reject" onclick="openRejectModal(<?php echo $request['id']; ?>)">Reject</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Pending Requests</h3>
                    <p>All book requests have been processed.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Recent Processed Requests</h2>
            
            <?php if (mysqli_num_rows($processed_requests) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = mysqli_fetch_assoc($processed_requests)): ?>
                        <tr>
                            <td><strong><?php echo $request['title']; ?></strong></td>
                            <td><?php echo $request['full_name']; ?></td>
                            <td><?php echo $request['student_number']; ?></td>
                            <td><?php echo formatDate($request['request_date']); ?></td>
                            <td>
                                <?php if ($request['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $request['admin_response']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No processed requests yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reject Request</h2>
                <span class="modal-close" onclick="closeRejectModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="form-group">
                    <label>Reason for Rejection *</label>
                    <textarea name="reason" required placeholder="Explain why this request is being rejected..."></textarea>
                </div>
                <button type="submit" name="reject_request" class="btn-reject">Reject Request</button>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(requestId) {
            document.getElementById('reject_request_id').value = requestId;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target === modal) {
                closeRejectModal();
            }
        }
    </script>
</body>
</html>