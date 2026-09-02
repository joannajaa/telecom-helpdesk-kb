<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();


        if ($user && password_verify($password, $user['password'])) {

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
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Helpdesk KB</title>
</head>
<body>
    <h2>Logowanie do Helpdesku</h2>
    <?php if ($error): ?>
        <p style="color: red;"><strong><?= htmlspecialchars($error) ?></strong></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
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
    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
</body>
</html>