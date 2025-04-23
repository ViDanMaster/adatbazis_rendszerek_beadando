<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
$parentLibrary = null;

if ($parentId) {
    $parentLibrary = getLibraryById($parentId);
    if (!$parentLibrary) {
        header('Location: index.php');
        exit;
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $user_id = $_SESSION['user_id'];
    $parentLibraryId = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    if (empty($name)) {
        $message = '<div class="error-message">A mappa neve nem lehet üres!</div>';
    } else {
        try {
            $newLibraryId = addLibraryWithParent($user_id, $name, $parentLibraryId);
            
            if ($parentLibraryId) {
                header("Location: library.php?id=$parentLibraryId&created=library");
            } else {
                header('Location: index.php?created=library');
            }
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
  <title>Új mappa - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a>
      <?php if ($parentLibrary): ?>
        &gt; <a href="library.php?id=<?php echo $parentId; ?>"><?php echo htmlspecialchars($parentLibrary['NAME']); ?></a>
      <?php endif; ?>
      &gt; <span>Új mappa</span>
    </div>
    
    <h1>Új mappa létrehozása</h1>
    
    <?php echo $message; ?>
    
    <div class="form-container">
      <form method="post" action="">
        <div class="form-group">
          <label for="name">Mappa neve:</label>
          <input type="text" id="name" name="name" required>
        </div>
        
        <?php if ($parentLibrary): ?>
          <div class="form-group">
            <label for="parent_id">Szülő mappa:</label>
            <input type="text" value="<?php echo htmlspecialchars($parentLibrary['NAME']); ?>" disabled>
            <input type="hidden" name="parent_id" value="<?php echo $parentId; ?>">
          </div>
        <?php endif; ?>
        
        <div class="form-actions">
          <a href="<?php echo $parentLibrary ? 'library.php?id=' . $parentId : 'index.php'; ?>" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Létrehozás</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>