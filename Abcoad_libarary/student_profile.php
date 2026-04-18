<?php
require_once 'config.php';

if (!isStudent()) {
    header('Location: student_login.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get student data
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = $student_id"));

// Update profile
if (isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $query = "UPDATE students SET full_name = '$full_name', email = '$email', 
              phone = '$phone', address = '$address' WHERE id = $student_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $message = "Profile updated successfully!";
        $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = $student_id"));
    } else {
        $error = "Failed to update profile!";
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $student['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE students SET password = '$hashed' WHERE id = $student_id";
                
                if (mysqli_query($conn, $query)) {
                    $message = "Password changed successfully!";
                } else {
                    $error = "Failed to change password!";
                }
            } else {
                $error = "Password must be at least 6 characters!";
            }
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
            max-width: 1000px;
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

        .profile-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }

        .profile-sidebar {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 4em;
            color: white;
        }

        .student-id {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .student-id-label {
            color: #666;
            font-size: 0.85em;
        }

        .student-id-value {
            color: #333;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 5px;
        }

        .profile-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .profile-container {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">📚 Library System</div>
        <div class="navbar-menu">
            <a href="index.php">Dashboard</a>
            <a href="browse_books.php">Browse Books</a>
            <a href="my_books.php">My Books</a>
            <a href="my_requests.php">My Requests</a>
            <a href="student_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>👤 My Profile</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-picture">
                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                </div>
                <h3><?php echo $student['full_name']; ?></h3>
                <p style="color: #666; margin-top: 5px;"><?php echo $student['email']; ?></p>
                
                <div class="student-id">
                    <div class="student-id-label">Student ID</div>
                    <div class="student-id-value"><?php echo $student['student_id']; ?></div>
                </div>

                <div class="student-id" style="margin-top: 10px;">
                    <div class="student-id-label">Member Since</div>
                    <div class="student-id-value"><?php echo date('M Y', strtotime($student['created_at'])); ?></div>
                </div>
            </div>

            <div class="profile-content">
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('profile')">Profile Information</button>
                    <button class="tab" onclick="switchTab('password')">Change Password</button>
                </div>

                <div id="profile" class="tab-content active">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="full_name" value="<?php echo $student['full_name']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" value="<?php echo $student['phone']; ?>">
                            </div>

                            <div class="form-group">
                                <label>Student ID (Read Only)</label>
                                <input type="text" value="<?php echo $student['student_id']; ?>" readonly style="background: #f8f9fa;">
                            </div>

                            <div class="form-group full-width">
                                <label>Address</label>
                                <textarea name="address"><?php echo $student['address']; ?></textarea>
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn">Update Profile</button>
                    </form>
                </div>

                <div id="password" class="tab-content">
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" required minlength="6">
                        </div>

                        <button type="submit" name="change_password" class="btn">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
        }
    </script>
</body>
</html>