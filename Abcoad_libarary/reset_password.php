<?php
require_once 'config.php';

$message = '';
$error = '';
$valid_token = false;

$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'student';

// Verify token
if ($token) {
    $query = "SELECT * FROM password_reset WHERE token = '$token' AND user_type = '$type' AND expires_at > NOW()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $valid_token = true;
        $reset_data = mysqli_fetch_assoc($result);
    }
}

 else {
        $error = "Invalid or expired reset token!";
    }


// Handle password reset
if (isset($_POST['reset_password']) && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        if (strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $reset_data['email'];
            $table = $type === 'admin' ? 'admin' : 'students';
            
            // Update password
            $update = "UPDATE $table SET password = '$hashed' WHERE email = '$email'";
            
            if (mysqli_query($conn, $update)) {
                // Delete used token
                mysqli_query($conn, "DELETE FROM password_reset WHERE token = '$token'");
                $message = "Password reset successful! You can now login with your new password.";
                $valid_token = false;
            } else {
                $error = "Failed to reset password!";
            }
        } else {
            $error = "Password must be at least 6 characters!";
        }
    } else {
        $error = "Passwords do not match!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">🔑</div>
            <h1>Reset Password</h1>
            <p>Create a new password for your account</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($valid_token): ?>
            <form method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password" required minlength="6">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" required minlength="6">
                </div>

                <button type="submit" name="reset_password" class="btn">Reset Password</button>
            </form>
        <?php endif; ?>

        <div class="links">
            <?php if ($message): ?>
                <?php if ($type === 'student'): ?>
                    <a href="student_login.php">← Go to Student Login</a>
                <?php else: ?>
                    <a href="admin_login.php">← Go to Admin Login</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="forgot_password.php?type=<?php echo $type; ?>">← Request New Reset Link</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
