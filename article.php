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

    if ($_POST['action'] === 'toggle_favorite') {
        $favoriteStmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND article_id = ?');
        $favoriteStmt->execute([$_SESSION['user_id'], $articleId]);

        if ($favoriteStmt->fetchColumn() !== false) {
            $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND article_id = ?')
                ->execute([$_SESSION['user_id'], $articleId]);
        } else {
            $pdo->prepare('INSERT INTO favorites (user_id, article_id) VALUES (?, ?)')
                ->execute([$_SESSION['user_id'], $articleId]);
        }

        header("Location: article.php?id=$articleId");
        exit;
    }

    if ($_POST['action'] === 'delete_comment') {
        $commentId = (int)($_POST['comment_id'] ?? 0);
        $commentStmt = $pdo->prepare('SELECT user_id FROM comments WHERE id = ? AND article_id = ?');
        $commentStmt->execute([$commentId, $articleId]);
        $commentOwnerId = $commentStmt->fetchColumn();

        if ($commentOwnerId === false || ((int)$commentOwnerId !== (int)$_SESSION['user_id'] && ($_SESSION['role'] ?? '') !== 'admin')) {
            die('Brak uprawnień do moderowania komentarzy.');
        }

        if ($commentId > 0) {
            $deleteComment = $pdo->prepare('DELETE FROM comments WHERE id = ? AND article_id = ?');
            $deleteComment->execute([$commentId, $articleId]);
        }

        header("Location: article.php?id=$articleId#comments");
        exit;
    }

    if ($_POST['action'] === 'edit_comment') {
        $commentId = (int)($_POST['comment_id'] ?? 0);
        $content = trim($_POST['comment_content'] ?? '');
        $commentStmt = $pdo->prepare('SELECT user_id FROM comments WHERE id = ? AND article_id = ?');
        $commentStmt->execute([$commentId, $articleId]);
        $commentOwnerId = $commentStmt->fetchColumn();

        if ($commentOwnerId === false || (int)$commentOwnerId !== (int)$_SESSION['user_id']) {
            die('Nie masz uprawnień do edycji tego komentarza.');
        }

        if ($content === '') {
            $commentError = 'Treść komentarza nie może być pusta.';
        } else {
            $updateComment = $pdo->prepare('UPDATE comments SET content = ? WHERE id = ? AND article_id = ?');
            $updateComment->execute([$content, $commentId, $articleId]);
            header("Location: article.php?id=$articleId#comments");
            exit;
        }
    }

    if ($_POST['action'] === 'report_article') {
        $reason = trim($_POST['report_reason'] ?? '');
        if ($reason === '') {
            $commentError = 'Podaj powód zgłoszenia.';
        } else {
            $reportStmt = $pdo->prepare('INSERT INTO article_reports (article_id, reporter_id, reason) VALUES (?, ?, ?)');
            $reportStmt->execute([$articleId, $_SESSION['user_id'], $reason]);
            $recipients = [];
            if ((int)$article['user_id'] !== (int)$_SESSION['user_id']) {
                $recipients[(int)$article['user_id']] = true;
            }
            $adminStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
            foreach ($adminStmt->fetchAll(PDO::FETCH_COLUMN) as $adminId) {
                if ((int)$adminId !== (int)$_SESSION['user_id']) {
                    $recipients[(int)$adminId] = true;
                }
            }
            $notificationStmt = $pdo->prepare('INSERT INTO notifications (user_id, article_id, message) VALUES (?, ?, ?)');
            foreach (array_keys($recipients) as $recipientId) {
                $notificationStmt->execute([$recipientId, $articleId, ($_SESSION['username'] ?? 'Użytkownik') . ' zgłosił nieaktualność Twojego artykułu.']);
            }
            header("Location: article.php?id=$articleId");
            exit;
        }
    }

    if ($_POST['action'] === 'add_comment') {
        $content = trim($_POST['comment_content'] ?? '');
        $parentCommentId = (int)($_POST['parent_comment_id'] ?? 0);
    }

    if ($_POST['action'] === 'add_comment' && !empty($content)) {
        $parentCommentId = $parentCommentId > 0 ? $parentCommentId : null;
        $stmtInsert = $pdo->prepare("INSERT INTO comments (article_id, user_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$articleId, $_SESSION['user_id'], $content, $parentCommentId]);
        $commentId = (int)$pdo->lastInsertId();

        $commenterName = $_SESSION['username'] ?? 'Użytkownik';
        $recipients = [];
        if ((int)$article['user_id'] !== (int)$_SESSION['user_id']) {
            $recipients[(int)$article['user_id']] = $parentCommentId !== null
                ? "$commenterName odpowiedział na komentarz w Twoim artykule."
                : "$commenterName dodał komentarz do Twojego artykułu.";
        }
        if ($parentCommentId !== null) {
            $parentOwnerStmt = $pdo->prepare('SELECT user_id FROM comments WHERE id = ? AND article_id = ?');
            $parentOwnerStmt->execute([$parentCommentId, $articleId]);
            $parentOwnerId = $parentOwnerStmt->fetchColumn();
            if ($parentOwnerId !== false && (int)$parentOwnerId !== (int)$_SESSION['user_id']) {
                $recipients[(int)$parentOwnerId] = "$commenterName odpowiedział na Twój komentarz.";
            }
        }
        $notificationStmt = $pdo->prepare('INSERT INTO notifications (user_id, comment_id, message) VALUES (?, ?, ?)');
        foreach ($recipients as $recipientId => $message) {
            if ((int)$recipientId !== (int)$_SESSION['user_id']) {
                $notificationStmt->execute([$recipientId, $commentId, $message]);
            }
        }
        header("Location: article.php?id=$articleId#comments");
        exit;
    } elseif ($_POST['action'] === 'add_comment') {
        $commentError = 'Treść komentarza nie może być pusta.';
    }
}

$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $favoriteStmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND article_id = ?');
    $favoriteStmt->execute([$_SESSION['user_id'], $articleId]);
    $isFavorite = $favoriteStmt->fetchColumn() !== false;
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

$stmtComments = $pdo->prepare("SELECT c.*, u.username, parent_u.username AS parent_username
                              FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              LEFT JOIN comments parent_c ON parent_c.id = c.parent_comment_id
                              LEFT JOIN users parent_u ON parent_u.id = parent_c.user_id
                              WHERE c.article_id = ? 
                              ORDER BY c.created_at ASC");
$stmtComments->execute([$articleId]);
$comments = $stmtComments->fetchAll();
$commentsByParent = [];
foreach ($comments as $comment) {
    $parentId = (int)($comment['parent_comment_id'] ?? 0);
    $commentsByParent[$parentId][] = $comment;
}

$orderedComments = [];
$appendComments = function (int $parentId) use (&$appendComments, &$orderedComments, $commentsByParent): void {
    foreach ($commentsByParent[$parentId] ?? [] as $comment) {
        $orderedComments[] = $comment;
        $appendComments((int)$comment['id']);
    }
};
$appendComments(0);
$comments = $orderedComments;

require_once 'includes/header.php';
?>

<article class="article-details">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
        <h2><?= htmlspecialchars($article['title']) ?></h2>
        <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="article.php?id=<?= $article['id'] ?>" class="favorite-form">
                    <input type="hidden" name="action" value="toggle_favorite">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="favorite-button"><?= $isFavorite ? '★ Usuń z ulubionych' : '☆ Dodaj do ulubionych' ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <p class="meta">
        📁 Kategoria: <a class="category-link" href="index.php?cat=<?= $article['category_id'] ?>"><strong><?= htmlspecialchars($article['category_name']) ?></strong></a> |
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

    <?php if (!empty($article['is_archived'])): ?>
        <p class="archived-badge">Artykuł archiwalny</p>
    <?php endif; ?>

    <section class="opinion-card">
        <div class="rating-section">
            <h4>Czy ta instrukcja pomogła rozwiązać zgłoszenie?</h4>
            <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                <button id="like-btn" data-id="<?= $article['id'] ?>" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                    <span id="like-icon" aria-label="Lubię to">👍</span>
                    <span id="likes-count"><?= $likesCount ?></span>
                </button>
                <span id="rating-feedback" style="font-size: 0.9rem;"></span>
            </div>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <small style="color: var(--text-muted); display: block; margin-top: 6px;">Zaloguj się, aby ocenić ten artykuł.</small>
            <?php endif; ?>
        </div>

        <div class="reactions-section">
            <h4>Reakcje:</h4>
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
    </section>

    <?php if ($isAuthorOrAdmin): ?>
        <div class="admin-actions" style="margin: 20px 0; padding: 12px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 6px;">
            <strong>Zarządzaj artykułem:</strong>
            <a href="edit_article.php?id=<?= $article['id'] ?>">Edytuj</a> | 
            <a href="delete_article.php?id=<?= $article['id'] ?>&token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>" onclick="return confirm('Na pewno chcesz usunąć ten artykuł?');" style="color: #ef4444;">Usuń</a>
        </div>
    <?php endif; ?>

    <div class="print-action" style="margin: 20px 0;">
        <button type="button" class="print-button" onclick="window.print()" style="padding: 6px 12px; font-size: 0.85rem;">
            🖨️ Drukuj instrukcję
        </button>
    </div>

    <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$article['user_id']): ?>
        <form method="POST" action="article.php?id=<?= $article['id'] ?>" class="report-form">
            <input type="hidden" name="action" value="report_article">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <label for="report_reason">Zgłoś nieaktualność artykułu:</label>
            <textarea id="report_reason" name="report_reason" rows="2" placeholder="Opisz, co wymaga aktualizacji..." required></textarea>
            <button type="submit" class="report-button">Zgłoś</button>
        </form>
    <?php endif; ?>

    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 25px 0;">

    <section id="comments" class="comments-section" style="margin-top: 30px;">
        <h3>Komentarze (<?= count($comments) ?>)</h3>

        <div class="comments-list" style="margin: 20px 0; display: flex; flex-direction: column; gap: 15px;">
            <?php if (empty($comments)): ?>
                <p style="color: var(--text-muted);">Brak uwag do tej procedury. Bądź pierwszą osobą, która doda notatkę!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div id="comment-<?= $comment['id'] ?>" class="comment-item <?= !empty($comment['parent_comment_id']) ? 'comment-reply' : '' ?>" style="padding: 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card, #fff);">
                        <?php $canEditComment = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$comment['user_id']; ?>
                        <div class="comment-header">
                            <strong>
                                <?= htmlspecialchars($comment['username']) ?>
                                <?php if (!empty($comment['parent_username'])): ?>
                                    &gt; <?= htmlspecialchars($comment['parent_username']) ?>
                                <?php endif; ?>
                            </strong>
                            <span><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div style="line-height: 1.5;">
                            <?php if ($canEditComment && isset($_GET['edit_comment']) && (int)$_GET['edit_comment'] === (int)$comment['id']): ?>
                                <form method="POST" action="article.php?id=<?= $article['id'] ?>#comments">
                                    <input type="hidden" name="action" value="edit_comment">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <?php if ($commentError && isset($_POST['comment_id']) && (int)$_POST['comment_id'] === (int)$comment['id']): ?>
                                        <p style="color: #ef4444;"><strong><?= htmlspecialchars($commentError) ?></strong></p>
                                    <?php endif; ?>
                                    <textarea name="comment_content" rows="3" style="width: 100%;" required><?= htmlspecialchars($comment['content']) ?></textarea>
                                    <button type="submit" style="margin-top: 8px;">Zapisz</button>
                                    <a href="article.php?id=<?= $article['id'] ?>#comments" style="margin-left: 8px;">Anuluj</a>
                                </form>
                            <?php else: ?>
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($_SESSION['user_id']) || $canEditComment || ($_SESSION['role'] ?? '') === 'admin'): ?>
                            <div class="comment-actions">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button type="button" class="comment-action reply-toggle" data-target="reply-form-<?= $comment['id'] ?>">Odpowiedz</button>
                                <?php endif; ?>
                                <?php if ($canEditComment): ?>
                                    <a class="comment-action" href="article.php?id=<?= $article['id'] ?>&edit_comment=<?= $comment['id'] ?>#comments">Edytuj</a>
                                <?php endif; ?>
                                <?php if ($canEditComment || ($_SESSION['role'] ?? '') === 'admin'): ?>
                                    <form method="POST" action="article.php?id=<?= $article['id'] ?>" class="comment-delete-form">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="comment-action comment-delete-button" onclick="return confirm('Usunąć ten komentarz?');">Usuń</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form id="reply-form-<?= $comment['id'] ?>" method="POST" action="article.php?id=<?= $article['id'] ?>#comments" class="reply-form" hidden>
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="parent_comment_id" value="<?= $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="text" name="comment_content" placeholder="Odpowiedz na komentarz..." required>
                                <button type="submit">Dodaj</button>
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
                    <label for="comment_content"><strong>Dodaj komentarz:</strong></label><br>
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