<?php
require "../includes/auth.php";
require_login();
require "../includes/csrf.php";
require "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM watchlist_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    }
}
header("Location: index.php");
exit;
