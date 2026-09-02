<?php
session_start();
require_once 'includes/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        die('Błąd weryfikacji żądania (CSRF).');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($userId <= 0 || !in_array($role, ['user', 'admin'], true)) {
            $message = 'Nieprawidłowe dane użytkownika.';
        } elseif ($userId === (int)$_SESSION['user_id'] && ($role !== 'admin' || $isActive !== 1)) {
            $message = 'Nie możesz odebrać uprawnień ani dezaktywować własnego konta.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET role = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$role, $isActive, $userId]);
            $message = 'Dane użytkownika zostały zaktualizowane.';
        }
    } elseif ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $message = 'Nazwa kategorii jest wymagana.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            $message = 'Kategoria została dodana.';
        }
    } elseif ($action === 'delete_category') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $articleCountStmt = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE category_id = ?');
        $articleCountStmt->execute([$categoryId]);
        $articleCount = (int)$articleCountStmt->fetchColumn();

        if ($categoryId <= 0) {
            $message = 'Nieprawidłowa kategoria.';
        } elseif ($articleCount > 0) {
            $message = 'Nie można usunąć kategorii, która zawiera artykuły.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$categoryId]);
            $message = 'Kategoria została usunięta.';
        }
    } elseif ($action === 'update_report') {
        $reportId = (int)($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($reportId <= 0 || !in_array($status, ['open', 'resolved', 'rejected'], true)) {
            $message = 'Nieprawidłowe zgłoszenie.';
        } else {
            $reportStmt = $pdo->prepare(
                'SELECT article_id, reporter_id FROM article_reports WHERE id = ?'
            );
            $reportStmt->execute([$reportId]);
            $report = $reportStmt->fetch();

            if (!$report) {
                $message = 'Nie znaleziono zgłoszenia.';
            } else {
                $stmt = $pdo->prepare('UPDATE article_reports SET status = ?, resolved_by = ? WHERE id = ?');
                $stmt->execute([$status, $_SESSION['user_id'], $reportId]);

                $statusLabels = [
                    'open' => 'ponownie otwarte',
                    'resolved' => 'rozpatrzone',
                    'rejected' => 'odrzucone',
                ];
                $notificationStmt = $pdo->prepare(
                    'INSERT INTO notifications (user_id, article_id, message) VALUES (?, ?, ?)'
                );
                $notificationStmt->execute([
                    $report['reporter_id'],
                    $report['article_id'],
                    'Administrator oznaczył Twoje zgłoszenie jako: ' . $statusLabels[$status] . '.',
                ]);
                $message = 'Status zgłoszenia został zaktualizowany.';
            }
        }
    }
}

$stats = [
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'articles' => (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
    'comments' => (int)$pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
    'ratings' => (int)$pdo->query('SELECT COUNT(*) FROM ratings')->fetchColumn(),
];

$users = $pdo->query('SELECT id, username, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$categories = $pdo->query(
    'SELECT c.id, c.name, COUNT(a.id) AS article_count
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC'
)->fetchAll();
$reports = $pdo->query(
    'SELECT ar.id, ar.reason, ar.status, ar.created_at, a.id AS article_id, a.title,
            u.username AS reporter
     FROM article_reports ar
     JOIN articles a ON a.id = ar.article_id
     JOIN users u ON u.id = ar.reporter_id
     WHERE ar.status = "open"
     ORDER BY ar.created_at DESC'
)->fetchAll();
require_once 'includes/header.php';
?>

<h2 class="page-heading">Panel administratora</h2>

<?php if ($message): ?>
    <p class="admin-message"><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<section class="admin-stats">
    <?php foreach (['users' => 'Użytkownicy', 'articles' => 'Artykuły', 'comments' => 'Komentarze', 'ratings' => 'Oceny'] as $key => $label): ?>
        <div class="admin-stat">
            <strong><?= $stats[$key] ?></strong>
            <span><?= $label ?></span>
        </div>
    <?php endforeach; ?>
</section>

<section class="admin-section admin-users-section">
    <h3>Użytkownicy</h3>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead><tr><th>Login</th><th>Rola</th><th>Aktywne konto</th><th>Akcja</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <select name="role" form="user-form-<?= $user['id'] ?>">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Użytkownik</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="is_active" value="1" form="user-form-<?= $user['id'] ?>" <?= $user['is_active'] ? 'checked' : '' ?>></td>
                    <td>
                        <form id="user-form-<?= $user['id'] ?>" method="POST" action="admin.php">
                            <input type="hidden" name="action" value="update_user">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit">Zapisz</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="admin-section admin-categories-section">
    <h3>Kategorie</h3>
    <form method="POST" action="admin.php" class="admin-category-form">
        <input type="hidden" name="action" value="add_category">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="name" placeholder="Nazwa nowej kategorii" required>
        <button type="submit">Dodaj kategorię</button>
    </form>
    <ul class="admin-list category-list">
        <?php foreach ($categories as $category): ?>
            <li>
                <span><strong><?= htmlspecialchars($category['name']) ?></strong> (<?= (int)$category['article_count'] ?> artykułów)</span>
                <?php if ((int)$category['article_count'] === 0): ?>
                    <form method="POST" action="admin.php">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="danger-button">Usuń</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="admin-section">
    <h3>Zgłoszenia nieaktualnych artykułów</h3>
    <div class="admin-reports">
        <?php if (empty($reports)): ?>
            <p>Brak otwartych zgłoszeń.</p>
        <?php else: ?>
            <?php foreach ($reports as $report): ?>
                <div class="admin-report">
                    <strong><a href="article.php?id=<?= $report['article_id'] ?>"><?= htmlspecialchars($report['title']) ?></a></strong>
                    <span>Od: <?= htmlspecialchars($report['reporter']) ?> · <?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></span>
                    <p><?= nl2br(htmlspecialchars($report['reason'])) ?></p>
                    <form method="POST" action="admin.php">
                        <input type="hidden" name="action" value="update_report">
                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <select name="status">
                            <?php foreach (['open' => 'Otwarte', 'resolved' => 'Rozpatrzone', 'rejected' => 'Odrzucone'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $report['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Zapisz</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
