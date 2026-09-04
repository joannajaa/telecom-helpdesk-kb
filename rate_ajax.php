<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany, aby oceniać.']);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Błąd weryfikacji żądania (CSRF).']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$articleId = (int)($data['article_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($articleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID artykułu.']);
    exit;
}

try {
    $articleCheck = $pdo->prepare(
        "SELECT a.id, c.name AS category_name
         FROM articles a
         JOIN categories c ON c.id = a.category_id
         WHERE a.id = ?"
    );
    $articleCheck->execute([$articleId]);
    $article = $articleCheck->fetch();
    if (!$article) {
        echo json_encode(['success' => false, 'message' => 'Nie znaleziono artykułu.']);
        exit;
    }
    if ($article['category_name'] === 'Nieaktualne') {
        echo json_encode(['success' => false, 'message' => 'Nieaktualnego artykułu nie można oceniać.']);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM ratings WHERE article_id = ? AND user_id = ?");
    $check->execute([$articleId, $userId]);
    $existing = $check->fetch();

    if ($existing) {
        $delete = $pdo->prepare("DELETE FROM ratings WHERE id = ?");
        $delete->execute([$existing['id']]);
        $action = 'removed';
        $message = 'Cofnięto ocenę.';
    } else {
        $insert = $pdo->prepare("INSERT INTO ratings (article_id, user_id, rating_value) VALUES (?, ?, 1)");
        $insert->execute([$articleId, $userId]);
        $action = 'added';
        $message = 'Dziękujemy za ocenę!';
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM ratings WHERE article_id = ? AND rating_value = 1");
    $countStmt->execute([$articleId]);
    $newTotal = (int)$countStmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'action' => $action,
        'message' => $message,
        'likes' => $newTotal
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd bazy danych.']);
}