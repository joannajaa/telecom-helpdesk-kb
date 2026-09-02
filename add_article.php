<?php
session_start();
require_once 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';


$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');
    $image_name  = null;


    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedExtensions)) {

            $image_name = uniqid('img_', true) . '.' . $fileExt;
            $uploadPath = 'uploads/' . $image_name;
            move_uploaded_file($fileTmpPath, $uploadPath);
        }
    }

    if (!empty($title) && !empty($content) && $category_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, image, category_id, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $image_name, $category_id, $_SESSION['user_id']]);

        header('Location: index.php');
        exit;
    } else {
        $message = 'Wypełnij wszystkie wymagane pola.';
    }
}

require_once 'includes/header.php';
?>

<h2>Dodaj nową procedurę techniczną</h2>

<?php if ($message): ?>
    <p style="color: red;"><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="add_article.php" method="POST" enctype="multipart/form-data">
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
        <input type="file" name="image" accept="image/*">
    </div>
    <br>
    <button type="submit">Zapisz artykuł</button>
</form>

<?php require_once 'includes/footer.php'; ?>