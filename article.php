<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$articleId = (int)($_GET['id'] ?? 0);


$stmt = $pdo->prepare("SELECT a.*, c.name AS category_name, u.username 
                       FROM articles a
                       JOIN categories c ON a.category_id = c.id
                       JOIN users u ON a.user_id = u.id
                       WHERE a.id = ?");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    echo "<p>Artykuł nie został znaleziony. <a href='index.php'>Wróć do listy</a></p>";
    require_once 'includes/footer.php';
    exit;
}


$stmtRatings = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM ratings WHERE article_id = ? AND rating_value = 1");
$stmtRatings->execute([$articleId]);
$likesCount = $stmtRatings->fetch()['total_likes'] ?? 0;

$isAuthorOrAdmin = isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $article['user_id'] || ($_SESSION['role'] ?? '') === 'admin');
?>

<article class="article-details">
    <h2><?= htmlspecialchars($article['title']) ?></h2>
    <p class="meta">
        📁 Kategoria: <a href="index.php?cat=<?= $article['category_id'] ?>"><strong><?= htmlspecialchars($article['category_name']) ?></strong></a> |
        👤 Dodane przez: <?= htmlspecialchars($article['username']) ?> |
        🕒 <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?>
    </p>

    <?php if (!empty($article['image']) && file_exists('uploads/' . $article['image'])): ?>
        <div style="margin: 20px 0;">
            <img src="uploads/<?= htmlspecialchars($article['image']) ?>" alt="Schemat techniczny" style="max-width: 100%; height: auto; border-radius: 4px;">
        </div>
    <?php endif; ?>

    <div class="content" style="line-height: 1.6; margin: 20px 0;">
        <?= nl2br(htmlspecialchars($article['content'])) ?>
    </div>


    <?php if ($isAuthorOrAdmin): ?>
        <div class="admin-actions" style="margin: 20px 0; padding: 10px; background: #eee;">
            <strong>Zarządzaj artykułem:</strong>
            <a href="edit_article.php?id=<?= $article['id'] ?>">✏️ Edytuj</a> | 
            <a href="delete_article.php?id=<?= $article['id'] ?>" onclick="return confirm('Na pewno chcesz usunąć ten artykuł?');" style="color: red;">🗑️ Usuń</a>
        </div>
    <?php endif; ?>

    <hr>


    <div class="rating-section" style="margin-top: 20px;">
        <h4>Czy ta instrukcja pomogła rozwiązać zgłoszenie?</h4>
        <button id="like-btn" data-id="<?= $article['id'] ?>">👍 Pomocna (<span id="likes-count"><?= $likesCount ?></span>)</button>
        <span id="rating-feedback" style="margin-left: 10px; color: green;"></span>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>