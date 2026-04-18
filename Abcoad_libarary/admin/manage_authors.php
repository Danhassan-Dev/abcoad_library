<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Add author
if (isset($_POST['add'])) {
    $name = sanitize($_POST['author_name']);
    $bio = sanitize($_POST['bio']);
    
    $query = "INSERT INTO authors (author_name, bio) VALUES ('$name', '$bio')";
    if (mysqli_query($conn, $query)) {
        $message = "Author added successfully!";
    } else {
        $error = "Failed to add author!";
    }
}

// Delete author
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    $query = "DELETE FROM authors WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $message = "Author deleted successfully!";
    } else {
        $error = "Cannot delete author! They may have books in the system.";
    }
}

// Update author
if (isset($_POST['update'])) {
    $id = sanitize($_POST['id']);
    $name = sanitize($_POST['author_name']);
    $bio = sanitize($_POST['bio']);
    
    $query = "UPDATE authors SET author_name = '$name', bio = '$bio' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $message = "Author updated successfully!";
    } else {
        $error = "Failed to update author!";
    }
}

// Get all authors with book counts
$authors = mysqli_query($conn, "
    SELECT a.*, COUNT(b.id) as book_count 
    FROM authors a
    LEFT JOIN books b ON a.id = b.author_id
    GROUP BY a.id
    ORDER BY a.author_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors</title>
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

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 24px;
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

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            padding: 6px 12px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
        }

        .btn-delete {
            padding: 6px 12px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            opacity: 0.8;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            background: #e3f2fd;
            color: #1976d2;
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

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .content-grid {
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
            <h1>✍️ Manage Authors</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="card">
                <h2>Add New Author</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Author Name *</label>
                        <input type="text" name="author_name" required>
                    </div>

                    <div class="form-group">
                        <label>Biography</label>
                        <textarea name="bio" placeholder="Brief biography of the author..."></textarea>
                    </div>

                    <button type="submit" name="add" class="btn">Add Author</button>
                </form>
            </div>

            <div class="card">
                <h2>All Authors</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Author Name</th>
                            <th>Biography</th>
                            <th>Books</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        while ($author = mysqli_fetch_assoc($authors)): 
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><strong><?php echo $author['author_name']; ?></strong></td>
                            <td><?php echo substr($author['bio'], 0, 50) . (strlen($author['bio']) > 50 ? '...' : ''); ?></td>
                            <td><span class="badge"><?php echo $author['book_count']; ?> books</span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-edit" onclick="openEditModal(<?php echo $author['id']; ?>, '<?php echo addslashes($author['author_name']); ?>', '<?php echo addslashes($author['bio']); ?>')">Edit</a>
                                    <a href="?delete=<?php echo $author['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure? This author has <?php echo $author['book_count']; ?> book(s).')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Author</h2>
                <span class="modal-close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Author Name *</label>
                    <input type="text" name="author_name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Biography</label>
                    <textarea name="bio" id="edit_bio"></textarea>
                </div>
                <button type="submit" name="update" class="btn">Update Author</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, bio) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_bio').value = bio;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>