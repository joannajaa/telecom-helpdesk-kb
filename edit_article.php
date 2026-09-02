<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$articleId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article || ($article['user_id'] != $_SESSION['user_id'] && ($_SESSION['role'] ?? '') !== 'admin')) {
    die("Brak dostępu do edycji tego artykułu.");
}

$message = '';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        die('Błąd weryfikacji żądania (CSRF).');
    }

    $title       = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');
    $image_name  = $article['image'];
    
    $is_pinned = (($_SESSION['role'] ?? '') === 'admin' && isset($_POST['is_pinned'])) ? 1 : ((($_SESSION['role'] ?? '') === 'admin') ? 0 : (int)$article['is_pinned']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['image']['tmp_name']);

                $allowedMimes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (array_key_exists($mimeType, $allowedMimes)) {
                    $ext = $allowedMimes[$mimeType];
                    $newImageName = bin2hex(random_bytes(16)) . '.' . $ext;
                    $uploadPath = 'uploads/' . $newImageName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        if (!empty($article['image']) && file_exists('uploads/' . $article['image'])) {
                            unlink('uploads/' . $article['image']);
                        }
                        $image_name = $newImageName;
                    } else {
                        $message = 'Błąd podczas zapisywania pliku na serwerze.';
                    }
                } else {
                    $message = 'Niedozwolony format pliku. Akceptowane: JPG, PNG, WebP.';
                }
            } else {
                $message = 'Plik jest zbyt duży (maksymalny rozmiar: 2 MB).';
            }
        } else {
            $message = 'Wystąpił problem podczas przesyłania pliku.';
        }
    }

    if (empty($message)) {
        if (!empty($title) && !empty($content) && $category_id > 0) {
            $updateStmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, image = ?, category_id = ?, is_pinned = ? WHERE id = ?");
            $updateStmt->execute([$title, $content, $image_name, $category_id, $is_pinned, $articleId]);

            header("Location: article.php?id=$articleId");
            exit;
        } else {
            $message = 'Wszystkie pola są wymagane.';
        }
    }
}

require_once 'includes/header.php';
?>

<h2>Edycja procedury technicznej</h2>

<?php if ($message): ?>
    <p style="color: red;"><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="edit_article.php?id=<?= $articleId ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div>
        <label>Tytuł:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" style="width: 100%;" required>
    </div>
    <br>
    <div>
        <label>Kategoria:</label><br>
        <select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $article['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <label>Treść:</label><br>
        <textarea name="content" rows="8" style="width: 100%;" required><?= htmlspecialchars($article['content']) ?></textarea>
    </div>
    <br>
    <div>
        <label>Zmień zdjęcie (opcjonalnie):</label><br>
        <?php if (!empty($article['image'])): ?>
            <p>Aktualne zdjęcie: <em><?= htmlspecialchars($article['image']) ?></em></p>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    </div>
    <br>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <div>
            <label>
                <input type="checkbox" name="is_pinned" value="1" <?= !empty($article['is_pinned']) ? 'checked' : '' ?>>
                📌 Przypnij artykuł na górze listy
            </label>
        </div>
        <br>
    <?php endif; ?>
    <button type="submit">Zapisz zmiany</button>
    <a href="article.php?id=<?= $articleId ?>">Anuluj</a>
</form>

<?php require_once 'includes/footer.php'; ?>