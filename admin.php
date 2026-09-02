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

<?php require_once 'includes/footer.php'; ?>
