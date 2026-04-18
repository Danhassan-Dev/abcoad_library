<?php
// register.php
session_start();
require 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $errors[] = 'All fields are required.';
    } else {
        // check existing
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email already registered.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
        $stmt->execute([$name,$email,$hash]);
        $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name'=>$name, 'email'=>$email, 'role'=>'user'];
        header('Location: index.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="assets/styles.css"></head><body>
<div class="container">
  <h2>Register</h2>
  <?php if($errors): foreach($errors as $e): ?><div class="alert"><?php echo htmlspecialchars($e); ?></div><?php endforeach; endif; ?>
  <form method="POST">
    <div class="form-row"><input type="text" name="name" placeholder="Full name" required></div>
    <div class="form-row"><input type="email" name="email" placeholder="Email" required></div>
    <div class="form-row"><input type="password" name="password" placeholder="Password" required></div>
    <div class="form-row"><button class="btn" type="submit">Register</button></div>
  </form>
  <p>Already have account? <a href="login.php">Login here</a></p>
</div>
</body></html>
