<?php
// borrow.php - handle borrow request or show user's borrows
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    // check availability
    $stmt = $pdo->prepare('SELECT availability FROM books WHERE id=?');
    $stmt->execute([$book_id]);
    $b = $stmt->fetch();
    if ($b && $b['availability']) {
        $pdo->prepare('INSERT INTO borrow_records (user_id, book_id, borrow_date, status) VALUES (?, ?, CURDATE(), "borrowed")')
            ->execute([$user['id'], $book_id]);
        $pdo->prepare('UPDATE books SET availability=0 WHERE id=?')->execute([$book_id]);
        header('Location: borrow.php'); exit;
    } else {
        $msg = 'Book not available.';
    }
}

// fetch user's borrows
$stmt = $pdo->prepare('SELECT br.*, b.title FROM borrow_records br JOIN books b ON br.book_id=b.id WHERE br.user_id=? ORDER BY br.borrow_date DESC');
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>My Borrows</title><link rel="stylesheet" href="assets/styles.css"></head><body>
<div class="container">
  <header><h2>My Borrowed Books</h2><nav><a href="index.php" class="btn">Catalog</a> <a href="logout.php" class="btn">Logout</a></nav></header>
  <?php if(isset($msg)): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <table><thead><tr><th>#</th><th>Title</th><th>Borrow Date</th><th>Return Date</th><th>Status</th><th>Action</th></tr></thead><tbody>
  <?php foreach($records as $r): ?>
    <tr>
      <td><?php echo $r['id']; ?></td>
      <td><?php echo htmlspecialchars($r['title']); ?></td>
      <td><?php echo $r['borrow_date']; ?></td>
      <td><?php echo $r['return_date'] ?? '--'; ?></td>
      <td><?php echo $r['status']; ?></td>
      <td>
        <?php if($r['status']=='borrowed'): ?>
          <form method="POST" action="return.php" style="display:inline;">
            <input type="hidden" name="record_id" value="<?php echo $r['id']; ?>">
            <button class="btn" type="submit">Return</button>
          </form>
        <?php else: ?>--
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div></body></html>
