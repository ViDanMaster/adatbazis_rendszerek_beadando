<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($name)) {
        $message = '<div class="error-message">A mappa neve nem lehet üres!</div>';
    } else {
        try {
            addLibrary($user_id, $name);
            header('Location: index.php?created=library');
            exit;
        } catch (PDOException $e) {
            error_log("Adatbázis hiba: " . $e->getMessage());
            $message = '<div class="error-message">Hiba történt a mappa létrehozása közben!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Új mappa - Drive Klón</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a> &gt; <span>Új mappa</span>
    </div>
    
    <h1>Új mappa létrehozása</h1>
    
    <?php echo $message; ?>
    
    <div class="form-container">
      <form method="post" action="">
        <div class="form-group">
          <label for="name">Mappa neve:</label>
          <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-actions">
          <a href="index.php" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Létrehozás</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>