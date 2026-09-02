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
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $message = 'Użytkownik o takim loginie lub adresie e-mail już istnieje!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $insert->execute([$username, $email, $hashedPassword]);

            $message = 'Konto zostało utworzone! Możesz się teraz zalogować.';
        }
    } else {
        $message = 'Wszystkie pola są wymagane.';
    }
}

require_once 'includes/header.php';
?>

<h2>Rejestracja nowego konsultanta</h2>

<?php if ($message): ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<form action="register.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div>
        <label>Login:</label><br>
        <input type="text" name="username" required>
    </div>
    <br>
    <div>
        <label>E-mail:</label><br>
        <input type="email" name="email" required>
    </div>
    <br>
    <div>
        <label>Hasło:</label><br>
        <input type="password" name="password" required>
    </div>
    <br>
    <button type="submit">Zarejestruj się</button>
</form>

<p style="margin-top: 15px;">Masz już konto? <a href="login.php">Zaloguj się</a></p>

<?php require_once 'includes/footer.php'; ?>