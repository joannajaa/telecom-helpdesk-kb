<?php
require_once 'includes/db.php';
require_once 'includes/tags.php';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['cat'] ?? 0);
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
$sort = $_GET['sort'] ?? 'latest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$countSql = "SELECT COUNT(*) FROM articles a WHERE 1=1";
$countParams = [];

if (!empty($search)) {
    $countSql .= " AND MATCH(a.title, a.content) AGAINST(? IN NATURAL LANGUAGE MODE)";
    $countParams[] = $search;
}

if ($categoryFilter > 0) {
    $countSql .= " AND a.category_id = ?";
    $countParams[] = $categoryFilter;
}

foreach ($selectedTags as $tag) {
    $countSql .= " AND EXISTS (
        SELECT 1 FROM article_tags at
        JOIN tags t ON t.id = at.tag_id
        WHERE at.article_id = a.id AND t.name = ?
    )";
    $countParams[] = $tag;
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalArticles / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

$sql = "SELECT a.*, c.name AS category_name, u.username, 
               COUNT(r.id) AS upvotes_count
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN ratings r ON a.id = r.article_id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND MATCH(a.title, a.content) AGAINST(? IN NATURAL LANGUAGE MODE)";
    $params[] = $search;
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

if ($sort === 'popular') {
    $sql .= " ORDER BY a.is_pinned DESC, upvotes_count DESC, a.created_at DESC";
} else {
    $sql .= " ORDER BY a.is_pinned DESC, a.created_at DESC";
}

$sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>

<h2 class="page-heading">Baza Wiedzy Helpdesk</h2>

<form method="GET" action="index.php" class="filters-form">
    <input type="text" name="search" placeholder="Szukaj procedury lub błędu..." value="<?= htmlspecialchars($search) ?>">
    <?php foreach ($selectedTags as $tag): ?>
        <input type="hidden" name="tag[]" value="<?= htmlspecialchars($tag) ?>">
    <?php endforeach; ?>
    
    <select name="cat">
        <option value="0">Wszystkie działy</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort">
        <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Najnowsze</option>
        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Najwyżej oceniane</option>
    </select>
    
    <button type="submit">Filtruj</button>
    <?php if (!empty($search) || $categoryFilter > 0 || !empty($selectedTags) || $sort !== 'latest'): ?>
        <a href="index.php" class="clear-filter">Wyczyść filtry</a>
    <?php endif; ?>
</form>

<?php if (!empty($selectedTags)): ?>
    <div class="selected-tags" aria-label="Wybrane tagi">
        <span class="selected-tags-label">Wybrane tagi:</span>
        <?php foreach ($selectedTags as $tag): ?>
            <?php $remainingTags = array_values(array_diff($selectedTags, [$tag])); ?>
            <?php $removeParams = ['search' => $search, 'cat' => $categoryFilter, 'sort' => $sort]; ?>
            <?php if (!empty($remainingTags)) { $removeParams['tag'] = $remainingTags; } ?>
            <span class="selected-tag">
                #<?= htmlspecialchars($tag) ?>
                <a href="index.php?<?= htmlspecialchars(http_build_query($removeParams)) ?>" aria-label="Usuń tag <?= htmlspecialchars($tag) ?>">×</a>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="articles-list">
    <?php if (empty($articles)): ?>
        <p>Brak artykułów spełniających kryteria.</p>
    <?php else: ?>
        <?php foreach ($articles as $art): ?>
            <article class="article-card" style="<?= !empty($art['is_pinned']) ? 'border-left: 4px solid #f59e0b; background: rgba(245, 158, 11, 0.05);' : '' ?>">
                <?php if (!empty($art['image']) && file_exists('uploads/' . $art['image'])): ?>
                    <div class="article-thumbnail">
                        <a href="article.php?id=<?= $art['id'] ?>">
                            <img src="uploads/<?= htmlspecialchars($art['image']) ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="article-body">
                    <h3>
                        <?php if (!empty($art['is_pinned'])): ?>
                            <span style="color: #f59e0b; font-size: 0.9em; margin-right: 4px;" title="Przypięty artykuł">📌</span>
                        <?php endif; ?>
                        <?php if (!empty($art['is_archived'])): ?>
                            <span class="archived-badge">Archiwalny</span>
                        <?php endif; ?>
                        <a href="article.php?id=<?= $art['id'] ?>"><?= htmlspecialchars($art['title']) ?></a>
                    </h3>
                    <p class="article-meta">
                        Kategoria: 
                        <a class="category-link" href="index.php?cat=<?= $art['category_id'] ?>"><strong><?= htmlspecialchars($art['category_name']) ?></strong></a> 
                        | Autor: <?= htmlspecialchars($art['username']) ?> 
                        | <?= date('d.m.Y H:i', strtotime($art['created_at'])) ?>
                        | Oceny: <strong>+<?= (int)$art['upvotes_count'] ?></strong>
                    </p>
                    <?php $articleTags = getArticleTags($pdo, (int)$art['id']); ?>
                    <?php if (!empty($articleTags)): ?>
                        <div class="article-tags">
                            <?php foreach ($articleTags as $tag): ?>
                                <?php $tagParams = ['search' => $search, 'cat' => $categoryFilter, 'sort' => $sort, 'tag' => array_values(array_unique(array_merge($selectedTags, [$tag])))]; ?>
                                <a href="index.php?<?= htmlspecialchars(http_build_query($tagParams)) ?>" class="tag-link">#<?= htmlspecialchars($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p><?= htmlspecialchars(mb_substr($art['content'], 0, 150)) ?>...</p>
                    <a href="article.php?id=<?= $art['id'] ?>" class="read-more">Czytaj całą instrukcję &rarr;</a>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="display: flex; gap: 8px; justify-content: center; margin: 30px 0;">
                <?php if ($page > 1): ?>
                    <?php $prevParams = array_merge($_GET, ['page' => $page - 1]); ?>
                    <a href="index.php?<?= http_build_query($prevParams) ?>" class="page-link">&laquo; Poprzednia</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php $pageParams = array_merge($_GET, ['page' => $i]); ?>
                    <a href="index.php?<?= http_build_query($pageParams) ?>" 
                       class="page-link <?= $i === $page ? 'active' : '' ?>"
                       style="padding: 6px 12px; border: 1px solid var(--border-color); text-decoration: none; border-radius: 4px; <?= $i === $page ? 'background: var(--btn-bg, #2563eb); color: #fff;' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <?php $nextParams = array_merge($_GET, ['page' => $page + 1]); ?>
                    <a href="index.php?<?= http_build_query($nextParams) ?>" class="page-link">Następna &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>