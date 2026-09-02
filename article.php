<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/tags.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$articleId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, c.name AS category_name, u.username 
                       FROM articles a
                       JOIN categories c ON a.category_id = c.id
                       JOIN users u ON a.user_id = u.id
                       WHERE a.id = ?");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    require_once 'includes/header.php';
    echo "<p>Artykuł nie został znaleziony. <a href='index.php'>Wróć do listy</a></p>";
    require_once 'includes/footer.php';
    exit;
}

$articleTags = getArticleTags($pdo, $articleId);

$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('Błąd weryfikacji żądania (CSRF).');
    }

    if ($_POST['action'] === 'delete_comment') {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            die('Brak uprawnień do moderowania komentarzy.');
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId > 0) {
            $deleteComment = $pdo->prepare('DELETE FROM comments WHERE id = ? AND article_id = ?');
            $deleteComment->execute([$commentId, $articleId]);
        }

        header("Location: article.php?id=$articleId#comments");
        exit;
    }

    if ($_POST['action'] === 'add_comment') {
        $content = trim($_POST['comment_content'] ?? '');
    }

    if ($_POST['action'] === 'add_comment' && !empty($content)) {
        $stmtInsert = $pdo->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
        $stmtInsert->execute([$articleId, $_SESSION['user_id'], $content]);
        header("Location: article.php?id=$articleId#comments");
        exit;
    } elseif ($_POST['action'] === 'add_comment') {
        $commentError = 'Treść komentarza nie może być pusta.';
    }
}

$stmtRatings = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM ratings WHERE article_id = ? AND rating_value = 1");
$stmtRatings->execute([$articleId]);
$likesCount = (int)($stmtRatings->fetch()['total_likes'] ?? 0);

$userLiked = false;
if (isset($_SESSION['user_id'])) {
    $stmtUserRating = $pdo->prepare("SELECT id FROM ratings WHERE article_id = ? AND user_id = ?");
    $stmtUserRating->execute([$articleId, $_SESSION['user_id']]);
    $userLiked = (bool)$stmtUserRating->fetch();
}

$isAuthorOrAdmin = isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $article['user_id'] || ($_SESSION['role'] ?? '') === 'admin');

$emotes = [
    'ratge'     => ['file' => 'ratge.png',     'label' => 'Ratge'],
    'pepethink' => ['file' => 'pepethink.png', 'label' => 'PepeThink'],
    'pog'       => ['file' => 'pog.png',       'label' => 'Pog'],
    'shrug'     => ['file' => '67.gif',        'label' => 'Shrug'],
    'cringe'    => ['file' => 'cringe.png',    'label' => 'Cringe'],
    'pray'      => ['file' => 'pray.png',      'label' => 'Pray'],
    'noting'    => ['file' => 'noting.gif',    'label' => 'Noting']
];

$stmtReactionCounts = $pdo->prepare("SELECT emoji, COUNT(*) AS cnt FROM article_reactions WHERE article_id = ? GROUP BY emoji");
$stmtReactionCounts->execute([$articleId]);
$reactionCounts = $stmtReactionCounts->fetchAll(PDO::FETCH_KEY_PAIR);

$userReactions = [];
if (isset($_SESSION['user_id'])) {
    $stmtUserReactions = $pdo->prepare("SELECT emoji FROM article_reactions WHERE article_id = ? AND user_id = ?");
    $stmtUserReactions->execute([$articleId, $_SESSION['user_id']]);
    $userReactions = $stmtUserReactions->fetchAll(PDO::FETCH_COLUMN);
}

$stmtComments = $pdo->prepare("SELECT c.*, u.username 
                              FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              WHERE c.article_id = ? 
                              ORDER BY c.created_at ASC");
$stmtComments->execute([$articleId]);
$comments = $stmtComments->fetchAll();

require_once 'includes/header.php';
?>

<article class="article-details">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
        <h2><?= htmlspecialchars($article['title']) ?></h2>
        <button type="button" onclick="window.print()" style="flex-shrink: 0; padding: 6px 12px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
            🖨️ Drukuj instrukcję
        </button>
    </div>

    <p class="meta">
        📁 Kategoria: <a href="index.php?cat=<?= $article['category_id'] ?>"><strong><?= htmlspecialchars($article['category_name']) ?></strong></a> |
        👤 Dodane przez: <?= htmlspecialchars($article['username']) ?> |
        🕒 <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?>
    </p>

    <?php if (!empty($articleTags)): ?>
        <div class="article-tags">
            <?php foreach ($articleTags as $tag): ?>
                <a href="index.php?tag[]=<?= urlencode($tag) ?>" class="tag-link">#<?= htmlspecialchars($tag) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($article['image']) && file_exists('uploads/' . $article['image'])): ?>
        <div style="margin: 20px 0;">
            <img src="uploads/<?= htmlspecialchars($article['image']) ?>" alt="Załącznik" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid var(--border-color);">
        </div>
    <?php endif; ?>

    <div class="content" style="line-height: 1.6; margin: 20px 0;">
        <?= nl2br(htmlspecialchars($article['content'])) ?>
    </div>

    <?php if ($isAuthorOrAdmin): ?>
        <div class="admin-actions" style="margin: 20px 0; padding: 12px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 6px;">
            <strong>Zarządzaj artykułem:</strong>
            <a href="edit_article.php?id=<?= $article['id'] ?>">Edytuj</a> | 
            <a href="delete_article.php?id=<?= $article['id'] ?>&token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>" onclick="return confirm('Na pewno chcesz usunąć ten artykuł?');" style="color: #ef4444;">Usuń</a>
        </div>
    <?php endif; ?>

    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 25px 0;">

    <div class="reactions-section" style="margin-bottom: 20px;">
        <h4 style="margin-bottom: 8px;">Reakcje:</h4>
        <div class="emotes-bar" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <?php foreach ($emotes as $key => $emote): ?>
                <?php 
                    $isActive = in_array($key, $userReactions, true);
                    $count = (int)($reactionCounts[$key] ?? 0);
                ?>
                <button type="button" 
                        class="btn-emote <?= $isActive ? 'active' : '' ?>" 
                        data-emoji="<?= $key ?>" 
                        data-article="<?= $article['id'] ?>"
                        <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 6px; background: <?= $isActive ? 'rgba(37, 99, 235, 0.15)' : 'var(--bg-card, #fff)' ?>; cursor: <?= isset($_SESSION['user_id']) ? 'pointer' : 'not-allowed' ?>;">
                    <img src="assets/emotes/<?= $emote['file'] ?>" alt="<?= $emote['label'] ?>" style="width: 24px; height: 24px; object-fit: contain; vertical-align: middle;">
                    <span class="emote-count" style="font-size: 0.85rem; font-weight: bold;"><?= $count ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rating-section" style="margin-top: 20px;">
        <h4>Czy ta instrukcja pomogła rozwiązać zgłoszenie?</h4>
        <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
            <button id="like-btn" data-id="<?= $article['id'] ?>" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                <span id="like-icon">👍</span> <span id="like-text"><?= $userLiked ? 'Cofnij ocenę' : 'Pomocna' ?></span> (<span id="likes-count"><?= $likesCount ?></span>)
            </button>
            <span id="rating-feedback" style="font-size: 0.9rem;"></span>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <small style="color: var(--text-muted); display: block; margin-top: 6px;">Zaloguj się, aby ocenić ten artykuł.</small>
        <?php endif; ?>
    </div>

    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 25px 0;">

    <section id="comments" class="comments-section" style="margin-top: 30px;">
        <h3>Komentarze i notatki serwisowe (<?= count($comments) ?>)</h3>

        <div class="comments-list" style="margin: 20px 0; display: flex; flex-direction: column; gap: 15px;">
            <?php if (empty($comments)): ?>
                <p style="color: var(--text-muted);">Brak uwag do tej procedury. Bądź pierwszą osobą, która doda notatkę!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" style="padding: 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card, #fff);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-muted);">
                            <strong><?= htmlspecialchars($comment['username']) ?></strong>
                            <span><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div style="line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <form method="POST" action="article.php?id=<?= $article['id'] ?>" class="comment-moderation-form">
                                <input type="hidden" name="action" value="delete_comment">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <button type="submit" class="danger-button" onclick="return confirm('Usunąć ten komentarz?');">Usuń</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="article.php?id=<?= $article['id'] ?>" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="action" value="add_comment">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <?php if ($commentError): ?>
                    <p style="color: red;"><strong><?= htmlspecialchars($commentError) ?></strong></p>
                <?php endif; ?>

                <div>
                    <label for="comment_content"><strong>Dodaj notatkę serwisową:</strong></label><br>
                    <textarea id="comment_content" name="comment_content" rows="4" style="width: 100%; margin-top: 8px;" placeholder="Wpisz treść uwagi lub wskazówki do procedury..." required></textarea>
                </div>
                <button type="submit" style="margin-top: 10px;">Dodaj komentarz</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px; color: var(--text-muted);"><a href="login.php">Zaloguj się</a>, aby dodać notatkę techniczną.</p>
        <?php endif; ?>
    </section>
</article>

<script>
document.querySelectorAll('.btn-emote').forEach(btn => {
    btn.addEventListener('click', async () => {
        const articleId = btn.dataset.article;
        const emoji = btn.dataset.emoji;
        btn.disabled = true;

        try {
            const response = await fetch('react_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ article_id: articleId, emoji: emoji })
            });
            const data = await response.json();

            if (data.success) {
                const countSpan = btn.querySelector('.emote-count');
                countSpan.textContent = data.count;
                if (data.action === 'added') {
                    btn.classList.add('active');
                    btn.style.background = 'rgba(37, 99, 235, 0.15)';
                } else {
                    btn.classList.remove('active');
                    btn.style.background = 'var(--bg-card, #fff)';
                }
            }
        } catch (error) {
            console.error(error);
        } finally {
            btn.disabled = false;
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>