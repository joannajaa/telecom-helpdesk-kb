<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        die('Błąd weryfikacji żądania (CSRF).');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();


        if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];


            header('Location: index.php');
            exit;
        } else {
            $error = 'Nieprawidłowy login lub hasło.';
        }
    } else {
        $error = 'Wypełnij wszystkie pola.';
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<h2 class="auth-heading">Logowanie</h2>
    <?php if ($error): ?>
        <p style="color: red;"><strong><?= htmlspecialchars($error) ?></strong></p>
    <?php endif; ?>

    <form action="login.php" method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div>
            <label>Login:</label><br>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Hasło:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <button type="submit">Zaloguj się</button>
    </form>
    <p class="auth-switch">Nie masz konta? <a href="register.php">Zarejestruj się</a></p>

<?php require_once 'includes/footer.php'; ?>