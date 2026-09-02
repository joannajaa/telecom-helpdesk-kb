<?php
session_start();
require_once 'includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Helpdesk KB</title>
</head>
<body>
    <h2>Rejestracja nowego konsultanta</h2>
    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div>
            <label>Login:</label><br>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>E-mail:</label><br>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Hasło:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <button type="submit">Zarejestruj się</button>
    </form>
    <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
</body>
</html>