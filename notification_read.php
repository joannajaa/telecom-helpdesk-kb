<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Błąd weryfikacji żądania (CSRF).']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$_SESSION['user_id']]);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true]);
    exit;
}

$notificationId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT n.comment_id, COALESCE(c.article_id, n.article_id) AS article_id
     FROM notifications n
     LEFT JOIN comments c ON c.id = n.comment_id
     WHERE n.id = ? AND n.user_id = ?'
);
$stmt->execute([$notificationId, $_SESSION['user_id']]);
$notification = $stmt->fetch();

if (!$notification) {
    header('Location: index.php');
    exit;
}

$markReadStmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
$markReadStmt->execute([$notificationId, $_SESSION['user_id']]);

$target = $notification['comment_id'] ? '#comment-' . (int)$notification['comment_id'] : '';
header('Location: article.php?id=' . (int)$notification['article_id'] . $target);
exit;
