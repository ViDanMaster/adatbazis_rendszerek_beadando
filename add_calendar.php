<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $color = $_POST['color'] ?? '';

    if (empty($name)) {
        $message = "<p class='error'>Név megadása kötelező!</p>";
    }  else {
        addCalendar($_SESSION['user_id'], $name,$color);
        $message = "<p class='message'>Naptár sikeresen létrehozva!</p>";

    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új naptár létrehozása - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
        <h1>Új naptár létrehozása</h1>
        <?php if (!empty($message)): ?>
            <div class="error-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">Naptár neve:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="colorr">Szín kiválasztása:</label>
                    <input type="color" id="color" name="color" value="#3498db">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Létrehozás</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>