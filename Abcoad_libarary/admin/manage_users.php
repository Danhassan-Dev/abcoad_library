<?php
// admin/manage_users.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role']!='admin') { header('Location: ../login.php'); exit; }

$users = $pdo->query('SELECT id,name,email,role FROM users')->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Manage Users</title><link rel="stylesheet" href="../assets/styles.css"></head><body>
<div class="container">
  <h2>Users</h2>
  <p><a href="dashboard.php" class="btn">Back</a></p>
  <table><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th></tr></thead><tbody>
    <?php foreach($users as $u): ?>
      <tr><td><?php echo $u['id']; ?></td><td><?php echo htmlspecialchars($u['name']); ?></td><td><?php echo htmlspecialchars($u['email']); ?></td><td><?php echo $u['role']; ?></td></tr>
    <?php endforeach; ?>
  </tbody></table>
</div></body></html>
