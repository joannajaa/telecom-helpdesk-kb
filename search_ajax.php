<?php
require_once 'includes/db.php';
require_once 'includes/tags.php';

header('Content-Type: application/json; charset=utf-8');

$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'popular';
$rawTags = $_GET['tag'] ?? [];
$rawTags = is_array($rawTags) ? $rawTags : [$rawTags];
$selectedTags = [];

foreach ($rawTags as $rawTag) {
    $tag = mb_strtolower(trim((string)$rawTag));
    if ($tag !== '' && mb_strlen($tag) <= 50) {
        $selectedTags[$tag] = true;
    }
}

$selectedTags = array_keys($selectedTags);
$sql = "SELECT a.*, c.name AS category_name, u.username,
               COUNT(r.id) AS upvotes_count
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN ratings r ON a.id = r.article_id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $searchPattern = '%' . $search . '%';
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

if ($categoryFilter > 0) {
    $sql .= " AND a.category_id = ?";
    $params[] = $categoryFilter;
}

foreach ($selectedTags as $tag) {
    $sql .= " AND EXISTS (
        SELECT 1 FROM article_tags at
        JOIN tags t ON t.id = at.tag_id
        WHERE at.article_id = a.id AND t.name = ?
    )";
    $params[] = $tag;
}

$sql .= " GROUP BY a.id";
$sql .= $sort === 'latest'
    ? " ORDER BY a.is_pinned DESC, a.created_at DESC"
    : " ORDER BY a.is_pinned DESC, upvotes_count DESC, a.created_at DESC";
$sql .= " LIMIT 5";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

foreach ($articles as &$article) {
    $article['id'] = (int)$article['id'];
    $article['category_id'] = (int)$article['category_id'];
    $article['upvotes_count'] = (int)$article['upvotes_count'];
    $article['is_pinned'] = (int)$article['is_pinned'];
    $article['is_archived'] = (int)$article['is_archived'];
    $article['tags'] = getArticleTags($pdo, $article['id']);
}
unset($article);

echo json_encode(['articles' => $articles], JSON_UNESCAPED_UNICODE);
