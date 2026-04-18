<?php
// return.php - process returns and calculate simple fines
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    $rid = (int)$_POST['record_id'];
    // fetch record
    $stmt = $pdo->prepare('SELECT * FROM borrow_records WHERE id=? AND user_id=?');
    $stmt->execute([$rid, $user['id']]);
    $r = $stmt->fetch();
    if ($r && $r['status']=='borrowed') {
        $pdo->prepare('UPDATE borrow_records SET status="returned", return_date=CURDATE() WHERE id=?')->execute([$rid]);
        // make book available
        $pdo->prepare('UPDATE books SET availability=1 WHERE id=?')->execute([$r['book_id']]);
        // compute fine (14 days allowed, 50 per day)
        $borrow = new DateTime($r['borrow_date']);
        $now = new DateTime();
        $diff = $now->diff($borrow)->days;
        $allowed = 14;
        $fine = 0;
        if ($diff > $allowed) $fine = ($diff - $allowed) * 50;
        $_SESSION['message'] = 'Returned. Fine: ' . $fine . ' Naira.';
    }
}
header('Location: borrow.php'); exit;
?>