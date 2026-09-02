<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$stmt = $pdo->prepare(
    'SELECT a.*, c.name AS category_name, u.username
     FROM favorites f
     JOIN articles a ON a.id = f.article_id
     JOIN categories c ON c.id = a.category_id
     JOIN users u ON u.id = a.user_id
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$articles = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<h2 class="page-heading">Ulubione artykuły</h2>

<?php if (empty($articles)): ?>
    <p class="empty-state">Nie masz jeszcze zapisanych ulubionych artykułów.</p>
<?php else: ?>
    <div class="articles-list">
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if (!empty($article['image']) && file_exists('uploads/' . $article['image'])): ?>
                    <div class="article-thumbnail">
                        <a href="article.php?id=<?= $article['id'] ?>">
                            <img src="uploads/<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                        </a>
                    </div>
                <?php endif; ?>
                <div class="article-body">
                    <h3><a href="article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
                    <p class="article-meta">
                        Kategoria: <a class="category-link" href="index.php?cat=<?= $article['category_id'] ?>"><strong><?= htmlspecialchars($article['category_name']) ?></strong></a>
                        | Autor: <?= htmlspecialchars($article['username']) ?>
                    </p>
                    <p><?= htmlspecialchars(mb_substr($article['content'], 0, 150)) ?>...</p>
                    <div class="favorite-actions">
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">Czytaj całą instrukcję &rarr;</a>
                        <form method="POST" action="article.php?id=<?= $article['id'] ?>">
                            <input type="hidden" name="action" value="toggle_favorite">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="danger-button">Usuń z ulubionych</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
