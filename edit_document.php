<?php
session_start();
include 'functions.php';

// Ellenőrizzük, hogy van-e ID paraméter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$documentId = (int)$_GET['id'];

// Adatok lekérése
$document = getDocumentById($documentId);

if (!$document) {
    header('Location: index.php');
    exit;
}

// Mappák lekérése a legördülőhöz
$libraries = getLibraries();

// Űrlap feldolgozása
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $content = isset($_POST['content']) ? $_POST['content'] : null;
    $libraryId = isset($_POST['library_id']) && $_POST['library_id'] !== '' ? (int)$_POST['library_id'] : null;
    
    if (empty($name)) {
        $message = '<div class="error-message">A fájl neve nem lehet üres!</div>';
    } else {
        try {
            $result = updateDocumentContent($documentId, $name, $content, $libraryId);
            
            if ($result) {
                $message = '<div class="success-message">A dokumentum sikeresen frissítve!</div>';
                // Frissítjük a lokális változót is
                $document['NAME'] = $name;
                if ($content !== null) {
                    $document['FILE_PATH'] = $content;
                }
                $document['LIBRARY_ID'] = $libraryId;
            } else {
                $message = '<div class="error-message">Hiba történt a dokumentum frissítése közben!</div>';
            }
        } catch (PDOException $e) {
            error_log("Adatbázis hiba: " . $e->getMessage());
            $message = '<div class="error-message">Hiba történt a dokumentum frissítése közben!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dokumentum szerkesztése - Drive Klón</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a> &gt; <span>Dokumentum szerkesztése</span>
    </div>
    
    <h1>Dokumentum szerkesztése</h1>
    
    <?php echo $message; ?>
    
    <div class="form-container">
      <form method="post" action="">
        <div class="form-group">
          <label for="name">Dokumentum neve:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($document['NAME']); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="library_id">Mappa:</label>
          <select id="library_id" name="library_id">
            <option value="">-- Nincs mappában --</option>
            <?php foreach ($libraries as $lib): ?>
              <option value="<?php echo $lib['LIBRARY_ID']; ?>" <?php echo isset($document['LIBRARY_ID']) && $document['LIBRARY_ID'] == $lib['LIBRARY_ID'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($lib['NAME']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-actions">
          <a href="<?php echo isset($document['LIBRARY_ID']) && $document['LIBRARY_ID'] ? 'library.php?id=' . $document['LIBRARY_ID'] : 'index.php'; ?>" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Mentés</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>