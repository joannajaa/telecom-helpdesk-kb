<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['cat'] ?? 0);


$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();


$sql = "SELECT a.*, c.name AS category_name, u.username 
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $sql .= " AND a.category_id = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>

<h2>Baza Wiedzy Helpdesk</h2>


<form method="GET" action="index.php" style="margin-bottom: 25px; display: flex; gap: 10px;">
    <input type="text" name="search" placeholder="Szukaj procedury lub błędu..." value="<?= htmlspecialchars($search) ?>">
    
    <select name="cat">
        <option value="0">Wszystkie działy</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit">Szukaj</button>
    <?php if (!empty($search) || $categoryFilter > 0): ?>
        <a href="index.php" style="align-self: center;">Wyczyść filtry</a>
    <?php endif; ?>
</form>


<div class="articles-list">
    <?php if (empty($articles)): ?>
        <p>Brak artykułów spełniających kryteria.</p>
    <?php else: ?>
        <?php foreach ($articles as $art): ?>
            <article style="border-bottom: 1px solid #ddd; padding: 15px 0;">
                <h3>
                    <a href="article.php?id=<?= $art['id'] ?>"><?= htmlspecialchars($art['title']) ?></a>
                </h3>
                <p>
                    📁 Kategoria: 
                    <a href="index.php?cat=<?= $art['category_id'] ?>"><strong><?= htmlspecialchars($art['category_name']) ?></strong></a> 
                    | 👤 Autor: <?= htmlspecialchars($art['username']) ?> 
                    | 🕒 <?= date('d.m.Y H:i', strtotime($art['created_at'])) ?>
                </p>
                <p><?= htmlspecialchars(mb_substr($art['content'], 0, 150)) ?>...</p>
                <a href="article.php?id=<?= $art['id'] ?>">Czytaj całą instrukcję &rarr;</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>