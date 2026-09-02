<?php
session_start();
require_once 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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
    $title       = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');
    $image_name  = $article['image']; 


    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedExtensions)) {

            if (!empty($article['image']) && file_exists('uploads/' . $article['image'])) {
                unlink('uploads/' . $article['image']);
            }
            $image_name = uniqid('img_', true) . '.' . $fileExt;
            move_uploaded_file($fileTmpPath, 'uploads/' . $image_name);
        }
    }

    if (!empty($title) && !empty($content) && $category_id > 0) {
        $updateStmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, image = ?, category_id = ? WHERE id = ?");
        $updateStmt->execute([$title, $content, $image_name, $category_id, $articleId]);

        header("Location: article.php?id=$articleId");
        exit;
    } else {
        $message = 'Wszystkie pola są wymagane.';
    }
}

require_once 'includes/header.php';
?>

<h2>Edycja procedury technicznej</h2>

<?php if ($message): ?>
    <p style="color: red;"><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="edit_article.php?id=<?= $articleId ?>" method="POST" enctype="multipart/form-data">
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
        <input type="file" name="image" accept="image/*">
    </div>
    <br>
    <button type="submit">Zapisz zmiany</button>
    <a href="article.php?id=<?= $articleId ?>">Anuluj</a>
</form>

<?php require_once 'includes/footer.php'; ?>