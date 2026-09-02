<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json; charset=utf-8');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany, aby oceniać.']);
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

    $check = $pdo->prepare("SELECT id FROM ratings WHERE article_id = ? AND user_id = ?");
    $check->execute([$articleId, $userId]);
    
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Już oceniłeś ten artykuł!']);
        exit;
    }


    $insert = $pdo->prepare("INSERT INTO ratings (article_id, user_id, rating_value) VALUES (?, ?, 1)");
    $insert->execute([$articleId, $userId]);


    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM ratings WHERE article_id = ? AND rating_value = 1");
    $countStmt->execute([$articleId]);
    $newTotal = $countStmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Dziękujemy za ocenę!',
        'new_likes' => $newTotal
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd bazy danych.']);
}