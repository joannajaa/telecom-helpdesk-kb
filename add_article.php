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
    $image_name  = null;
    $is_pinned   = (($_SESSION['role'] ?? '') === 'admin' && isset($_POST['is_pinned'])) ? 1 : 0;

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
                    $image_name = bin2hex(random_bytes(16)) . '.' . $ext;
                    $uploadPath = 'uploads/' . $image_name;

                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $image_name = null;
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
            $stmt = $pdo->prepare("INSERT INTO articles (title, content, image, category_id, user_id, is_pinned) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $content, $image_name, $category_id, $_SESSION['user_id'], $is_pinned]);

            header('Location: index.php');
            exit;
        } else {
            $message = 'Wypełnij wszystkie wymagane pola.';
        }
    }
}

require_once 'includes/header.php';
?>

<h2>Dodaj nową procedurę techniczną</h2>

<?php if ($message): ?>
    <p style="color: red;"><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="add_article.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div>
        <label>Tytuł problemu / artykułu:</label><br>
        <input type="text" name="title" style="width: 100%;" required>
    </div>
    <br>
    <div>
        <label>Kategoria:</label><br>
        <select name="category_id" required>
            <option value="">-- Wybierz dział --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <label>Opis rozwiązania / procedury:</label><br>
        <textarea name="content" rows="8" style="width: 100%;" required></textarea>
    </div>
    <br>
    <div>
        <label>Zdjęcie pomocnicze / schemat (opcjonalnie):</label><br>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    </div>
    <br>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <div>
            <label>
                <input type="checkbox" name="is_pinned" value="1">
                📌 Przypnij artykuł na górze listy
            </label>
        </div>
        <br>
    <?php endif; ?>
    <button type="submit">Zapisz artykuł</button>
</form>

<?php require_once 'includes/footer.php'; ?>