<?php
session_start();
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Kérjük, töltse ki az összes mezőt!';
    } else {
        $authResult = authenticateUser($username, $password);
        
        if ($authResult['success']) {
            $user = $authResult['user'];
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $user['USERNAME'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = $authResult['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - Goofle</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h4>Bejelentkezés</h4>
            </div>
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Felhasználónév</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-login">Bejelentkezés</button>
                </form>
            </div>
            <div class="login-footer">
                <p>Nincs még fiókja? <a href="register.php">Regisztráljon itt!</a></p>
            </div>
        </div>
    </div>
</body>
</html>