<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$notifications = [];
$unreadNotifications = 0;
if (isset($_SESSION['user_id'])) {
    $notificationStmt = $pdo->prepare(
        'SELECT n.id, n.message, n.is_read, n.created_at, COALESCE(c.article_id, n.article_id) AS article_id
         FROM notifications n
         LEFT JOIN comments c ON c.id = n.comment_id
         WHERE n.user_id = ?
         ORDER BY n.created_at DESC
         LIMIT 8'
    );
    $notificationStmt->execute([$_SESSION['user_id']]);
    $notifications = $notificationStmt->fetchAll();
    foreach ($notifications as $notification) {
        $unreadNotifications += (int)!$notification['is_read'];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk Knowledge Base</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <h1><a href="index.php">Telecom Helpdesk - Baza Wiedzy</a></h1>
        </div>
        <nav class="nav-links">
            <a href="index.php">Baza wiedzy</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add_article.php">Dodaj artykuł</a>
                <a href="favorites.php">Ulubione</a>
                <details class="notifications-menu">
                    <summary class="notification-summary <?= $unreadNotifications > 0 ? 'has-unread' : '' ?>" aria-label="Powiadomienia">
                        <svg class="notification-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
                        </svg>
                        <?php if ($unreadNotifications > 0): ?><span class="notification-badge"><?= $unreadNotifications ?></span><?php endif; ?>
                    </summary>
                    <div class="notifications-dropdown">
                        <?php if (empty($notifications)): ?>
                            <span>Brak powiadomień.</span>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="<?= !$notification['is_read'] ? 'unread' : '' ?>" href="notification_read.php?id=<?= $notification['id'] ?>">
                                    <?= htmlspecialchars($notification['message']) ?>
                                    <small><?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </details>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin.php">Panel admina</a>
                <?php endif; ?>
                <span class="user-badge">Konsultant: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <a href="logout.php" class="btn-logout">Wyloguj</a>
            <?php else: ?>
                <a href="login.php">Logowanie</a>
                <a href="register.php">Rejestracja</a>
            <?php endif; ?>

            <select id="theme-switch" class="theme-selector">
                <option value="light">☀️ Jasny</option>
                <option value="dark">🔵 Dark + Blue</option>
                <option value="dark-pink">🌸 Dark + Pink</option>
            </select>
        </nav>
    </header>
    <main class="container">