<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_name':
            $error = 'A fájl neve nem lehet üres!';
            break;
        case 'upload_failed':
            $error = 'A fájl feltöltése sikertelen!';
            break;
        case 'invalid_file':
            $error = 'Érvénytelen fájlformátum!';
            break;
        case 'file_too_large':
            $error = 'A fájl mérete túl nagy!';
            break;
        case 'database':
            $error = 'Adatbázis hiba történt!';
            break;
    }
}

$libraryId = isset($_GET['library_id']) ? (int)$_GET['library_id'] : '';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Új fájl feltöltése - Drive Klón</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a> &gt; <span>Új fájl feltöltése</span>
    </div>
    
    <h1>Új fájl feltöltése</h1>
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
      <form method="post" action="save_document.php" enctype="multipart/form-data">
        <div class="form-group">
          <label for="library_id">Mappa:</label>
          <select id="library_id" name="library_id">
            <option value="">-- Nincs mappában --</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="file">Fájl kiválasztása:</label>
          <input type="file" id="file" name="uploaded_file" required>
        </div>
        
        <div class="form-actions">
          <a href="index.php" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Feltöltés</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    fetch('get_libraries.php')
      .then(response => response.json())
      .then(libraries => {
        const select = document.getElementById('library_id');
        const urlParams = new URLSearchParams(window.location.search);
        const selectedLibraryId = urlParams.get('library_id');
        
        libraries.forEach(lib => {
          const option = document.createElement('option');
          option.value = lib.LIBRARY_ID;
          option.textContent = lib.NAME;
          
          if (selectedLibraryId && selectedLibraryId == lib.LIBRARY_ID) {
            option.selected = true;
          }
          
          select.appendChild(option);
        });
      })
      .catch(error => console.error('Hiba a mappák betöltésekor:', error));
  </script>

</body>
</html>