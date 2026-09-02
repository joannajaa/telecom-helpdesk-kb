<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$articleId = (int)($data['article_id'] ?? 0);
$emoji = trim($data['emoji'] ?? '');

$allowedEmojis = [
    'ratge',
    'pepethink',
    'pog',
    'shrug',
    'cringe',
    'pray',
    'noting'
];

if ($articleId <= 0 || !in_array($emoji, $allowedEmojis, true)) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmtCheck = $pdo->prepare("SELECT id FROM article_reactions WHERE article_id = ? AND user_id = ? AND emoji = ?");
$stmtCheck->execute([$articleId, $userId, $emoji]);
$existing = $stmtCheck->fetch();

if ($existing) {
    $stmtDel = $pdo->prepare("DELETE FROM article_reactions WHERE id = ?");
    $stmtDel->execute([$existing['id']]);
    $action = 'removed';
} else {
    $stmtAdd = $pdo->prepare("INSERT INTO article_reactions (article_id, user_id, emoji) VALUES (?, ?, ?)");
    $stmtAdd->execute([$articleId, $userId, $emoji]);
    $action = 'added';
}

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM article_reactions WHERE article_id = ? AND emoji = ?");
$stmtCount->execute([$articleId, $emoji]);
$count = (int)$stmtCount->fetchColumn();

echo json_encode([
    'success' => true,
    'action'  => $action,
    'emoji'   => $emoji,
    'count'   => $count
]);