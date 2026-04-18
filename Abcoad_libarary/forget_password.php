<?php
require_once 'config.php';

$message = '';
$error = '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'student';

// Handle password reset request
if (isset($_POST['request_reset'])) {
    $email = sanitize($_POST['email']);
    
    // Check if email exists
    $table = $type === 'admin' ? 'admin' : 'students';
    $check = mysqli_query($conn, "SELECT id, full_name FROM $table WHERE email = '$email'");
    
    if (mysqli_num_rows($check) > 0) {
        $user = mysqli_fetch_assoc($check);
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $query = "INSERT INTO password_reset (email, token, user_type, expires_at) 
                  VALUES ('$email', '$token', '$type', '$expires')";
        
        if (mysqli_query($conn, $query)) {
            // In a real application, send email with reset link
            // For demo purposes, we'll display the reset link
            $reset_link = "reset_password.php?token=$token&type=$type";
            $message = "Password reset link generated! In a production environment, this would be sent to your email.<br><br>
                       <strong>Reset Link:</strong> <a href='$reset_link'>$reset_link</a><br><br>
                       This link will expire in 1 hour.";
        } else {
            $error = "Failed to generate reset link!";
        }
    } else {
        $error = "No account found with this email address!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5em;
        }

        .header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
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

        .alert-success a {
            color: #155724;
            font-weight: bold;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: scale(1.02);
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .info-box p {
            color: #1976d2;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .type-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e1e8ed;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .type-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">🔒</div>
            <h1>Forgot Password</h1>
            <p>Reset your account password</p>
        </div>

        <div class="type-selector">
            <a href="?type=student" class="type-btn <?php echo $type === 'student' ? 'active' : ''; ?>">Student</a>
            <a href="?type=admin" class="type-btn <?php echo $type === 'admin' ? 'active' : ''; ?>">Admin</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="info-box">
            <p>Enter your email address and we'll send you a link to reset your password. 
            <strong>Note:</strong> In this demo, the reset link will be displayed on screen. 
            In a production environment, it would be sent to your email.</p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit" name="request_reset" class="btn">Request Password Reset</button>
        </form>

        <div class="links">
            <?php if ($type === 'student'): ?>
                <a href="student_login.php">← Back to Student Login</a>
            <?php else: ?>
                <a href="admin_login.php">← Back to Admin Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>