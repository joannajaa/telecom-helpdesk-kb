<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        die('Błąd weryfikacji żądania (CSRF).');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $message = 'Użytkownik o takim loginie już istnieje!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, password, role, is_active) VALUES (?, ?, 'user', 1)");
            $insert->execute([$username, $hashedPassword]);

            $message = 'Konto zostało utworzone! Możesz się teraz zalogować.';
        }
    } else {
        $message = 'Wszystkie pola są wymagane.';
    }
}

require_once 'includes/header.php';
?>

<h2 class="auth-heading">Rejestracja</h2>

<?php if ($message): ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="register.php" method="POST" class="auth-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div>
        <label>Login:</label><br>
        <input type="text" name="username" required>
    </div>
    <br>
    <div>
        <label>Hasło:</label><br>
        <input type="password" name="password" required>
    </div>
    <br>
    <button type="submit">Zarejestruj się</button>
</form>

<p class="auth-switch">Masz już konto? <a href="login.php">Zaloguj się</a></p>

<?php require_once 'includes/footer.php'; ?>