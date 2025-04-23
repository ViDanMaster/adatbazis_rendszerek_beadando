<?php
session_start();
include 'functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$libraryId = (int)$_GET['id'];

$library = getLibraryById($libraryId);

if (!$library) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $message = '<div class="error-message">A mappa neve nem lehet üres!</div>';
    } else {
        try {
            updateLibrary($libraryId, $name);
            
            $message = '<div class="success-message">A mappa sikeresen frissítve!</div>';
            $library['NAME'] = $name;
        } catch (PDOException $e) {
            error_log("Adatbázis hiba: " . $e->getMessage());
            $message = '<div class="error-message">Hiba történt a mappa frissítése közben!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mappa szerkesztése - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a> &gt; <span>Mappa szerkesztése</span>
    </div>
    
    <h1>Mappa szerkesztése</h1>
    
    <?php echo $message; ?>
    
    <div class="form-container">
      <form method="post" action="">
        <div class="form-group">
          <label for="name">Mappa neve:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($library['NAME']); ?>" required>
        </div>
        
        <div class="form-actions">
          <a href="index.php" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Mentés</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>