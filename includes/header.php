<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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