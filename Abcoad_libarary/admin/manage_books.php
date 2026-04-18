<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Add book
if (isset($_POST['add'])) {
    $title = sanitize($_POST['title']);
    $isbn = sanitize($_POST['isbn']);
    $author_id = sanitize($_POST['author_id']);
    $category_id = sanitize($_POST['category_id']);
    $quantity = sanitize($_POST['quantity']);
    $publication_year = sanitize($_POST['publication_year']);
    $description = sanitize($_POST['description']);
    
    $check = mysqli_query($conn, "SELECT id FROM books WHERE isbn = '$isbn'");
    if (mysqli_num_rows($check) > 0) {
        $error = "A book with this ISBN already exists!";
    } else {
        $query = "INSERT INTO books (title, isbn, author_id, category_id, quantity, available_quantity, publication_year, description) 
                  VALUES ('$title', '$isbn', '$author_id', '$category_id', '$quantity', '$quantity', '$publication_year', '$description')";
        
        if (mysqli_query($conn, $query)) {
            $message = "Book added successfully!";
        } else {
            $error = "Failed to add book!";
        }
    }
}

// Delete book
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    $query = "DELETE FROM books WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $message = "Book deleted successfully!";
    } else {
        $error = "Cannot delete book! It may be currently issued.";
    }
}

// Update book
if (isset($_POST['update'])) {
    $id = sanitize($_POST['id']);
    $title = sanitize($_POST['title']);
    $isbn = sanitize($_POST['isbn']);
    $author_id = sanitize($_POST['author_id']);
    $category_id = sanitize($_POST['category_id']);
    $quantity = sanitize($_POST['quantity']);
    $publication_year = sanitize($_POST['publication_year']);
    $description = sanitize($_POST['description']);
    
    $query = "UPDATE books SET title = '$title', isbn = '$isbn', author_id = '$author_id', 
              category_id = '$category_id', quantity = '$quantity', publication_year = '$publication_year', 
              description = '$description' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $message = "Book updated successfully!";
    } else {
        $error = "Failed to update book!";
    }
}

// Get all books
$books = mysqli_query($conn, "
    SELECT b.*, a.author_name, c.category_name 
    FROM books b
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    ORDER BY b.title
");

// Get categories and authors for dropdowns
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$authors = mysqli_query($conn, "SELECT * FROM authors ORDER BY author_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
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
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
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

        .btn-toggle {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .add-form {
            display: none;
        }

        .add-form.active {
            display: block;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
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
            position: sticky;
            top: 0;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit {
            padding: 6px 12px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85em;
        }

        .btn-delete {
            padding: 6px 12px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85em;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
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
            max-width: 600px;
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

            .form-grid {
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
            <h1>📚 Manage Books</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <button class="btn-toggle" onclick="toggleAddForm()">➕ Add New Book</button>
            
            <div id="addForm" class="add-form">
                <h2>Add New Book</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Book Title *</label>
                            <input type="text" name="title" required>
                        </div>

                        <div class="form-group">
                            <label>ISBN *</label>
                            <input type="text" name="isbn" required placeholder="978-0-xxx-xxxxx-x">
                        </div>

                        <div class="form-group">
                            <label>Author *</label>
                            <select name="author_id" required>
                                <option value="">Select Author</option>
                                <?php 
                                mysqli_data_seek($authors, 0);
                                while ($author = mysqli_fetch_assoc($authors)): 
                                ?>
                                    <option value="<?php echo $author['id']; ?>"><?php echo $author['author_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                mysqli_data_seek($categories, 0);
                                while ($cat = mysqli_fetch_assoc($categories)): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" name="quantity" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Publication Year</label>
                            <input type="number" name="publication_year" min="1800" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>">
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" placeholder="Brief description of the book..."></textarea>
                        </div>
                    </div>

                    <button type="submit" name="add" class="btn">Add Book</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>All Books</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Available</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        while ($book = mysqli_fetch_assoc($books)): 
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><strong><?php echo $book['title']; ?></strong></td>
                            <td><?php echo $book['isbn']; ?></td>
                            <td><?php echo $book['author_name']; ?></td>
                            <td><?php echo $book['category_name']; ?></td>
                            <td><?php echo $book['quantity']; ?></td>
                            <td>
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <span class="badge badge-success"><?php echo $book['available_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">0</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $book['publication_year'] ?: 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-edit" onclick='openEditModal(<?php echo json_encode($book); ?>)'>Edit</a>
                                    <a href="?delete=<?php echo $book['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
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
                <h2>Edit Book</h2>
                <span class="modal-close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Book Title *</label>
                        <input type="text" name="title" id="edit_title" required>
                    </div>

                    <div class="form-group">
                        <label>ISBN *</label>
                        <input type="text" name="isbn" id="edit_isbn" required>
                    </div>

                    <div class="form-group">
                        <label>Author *</label>
                        <select name="author_id" id="edit_author" required>
                            <?php 
                            mysqli_data_seek($authors, 0);
                            while ($author = mysqli_fetch_assoc($authors)): 
                            ?>
                                <option value="<?php echo $author['id']; ?>"><?php echo $author['author_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="edit_category" required>
                            <?php 
                            mysqli_data_seek($categories, 0);
                            while ($cat = mysqli_fetch_assoc($categories)): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" id="edit_quantity" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Publication Year</label>
                        <input type="number" name="publication_year" id="edit_year" min="1800" max="<?php echo date('Y'); ?>">
                    </div>

                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" id="edit_description"></textarea>
                    </div>
                </div>
                <button type="submit" name="update" class="btn">Update Book</button>
            </form>
        </div>
    </div>

    <script>
        function toggleAddForm() {
            const form = document.getElementById('addForm');
            form.classList.toggle('active');
        }

        function openEditModal(book) {
            document.getElementById('edit_id').value = book.id;
            document.getElementById('edit_title').value = book.title;
            document.getElementById('edit_isbn').value = book.isbn;
            document.getElementById('edit_author').value = book.author_id;
            document.getElementById('edit_category').value = book.category_id;
            document.getElementById('edit_quantity').value = book.quantity;
            document.getElementById('edit_year').value = book.publication_year || '';
            document.getElementById('edit_description').value = book.description || '';
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