<?php
session_start();
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($password2) || empty($name)) {
        $message = "<p class='error'>Minden mező kitöltése kötelező!</p>";
    } elseif ($password !== $password2) {
        $message = "<p class='error'>A jelszavak nem egyeznek!</p>";
    } else {
        $regResult = registerUser($username, $password, $email, $name);
        if ($regResult['success']) {
            $user = $regResult['user'];
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $user['USERNAME'];
            $message = "<p class='message'>Sikeres regisztráció!</p>";

            header('Location: login.php');
        } else {
            $message = "<p class='error'>Hiba történt: " . $regResult['message']  . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
<form method="post">
    <h2>Regisztráció</h2>
    <input type="text" name="username" placeholder="Felhasználónév" required>
    <input type="text" name="name" placeholder="Teljes név" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Jelszó" required>
    <input type="password" name="password2" placeholder="Jelszó újra" required>
    <button type="submit">Regisztrálok</button>
    <a href="login.php" style="text-decoration: none;">
        <button type="button">Mégse</button>
    </a>
    <?= $message ?>
</form>
</body>
</html>

