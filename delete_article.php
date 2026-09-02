<?php
session_start();
require_once 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$articleId = (int)($_GET['id'] ?? 0);


$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$articleId]);
$article = $stmt->fetch();


if (!$article || ($article['user_id'] != $_SESSION['user_id'] && ($_SESSION['role'] ?? '') !== 'admin')) {
    die("Brak dostępu lub artykuł nie istnieje.");
}


if (!empty($article['image']) && file_exists('uploads/' . $article['image'])) {
    unlink('uploads/' . $article['image']);
}


$deleteStmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$deleteStmt->execute([$articleId]);


header('Location: index.php');
exit;