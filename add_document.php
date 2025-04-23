<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$libraryId = isset($_GET['library_id']) ? (int)$_GET['library_id'] : null;

$libraries = getEditableLibraries($_SESSION['user_id']);

$currentLibrary = null;
$canEdit = false;

if ($libraryId) {
    $currentLibrary = getLibraryById($libraryId);
    if ($currentLibrary) {
        $userPermission = getUserLibraryPermission($_SESSION['user_id'], $libraryId);
        $canEdit = ($userPermission === 'owner' || $userPermission === 'edit');
    }
    
    if (!$currentLibrary || !$canEdit) {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libraryId = isset($_POST['library_id']) && !empty($_POST['library_id']) ? (int)$_POST['library_id'] : null;
    
    if ($libraryId) {
        $userPermission = getUserLibraryPermission($_SESSION['user_id'], $libraryId);
        $canEdit = ($userPermission === 'owner' || $userPermission === 'edit');
        
        if (!$canEdit) {
            $error = 'Nincs jogosultságod ehhez a mappához fájlt feltölteni!';
        }
    }
    
    if (empty($error) && (!isset($_FILES['uploaded_file']) || $_FILES['uploaded_file']['error'] !== UPLOAD_ERR_OK)) {
        $error = 'A fájl feltöltése sikertelen!';
    } elseif (empty($error)) {
        $file = $_FILES['uploaded_file'];
        $fileName = $file['name'];
        $fileType = $file['type'];
        $fileSize = $file['size'];
        $tempPath = $file['tmp_name'];
        
        $userId = $_SESSION['user_id'];
        $uploadsDir = "uploads/user_{$userId}/";
        
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        
        $newFileName = uniqid() . '_' . $fileName;
        $destination = $uploadsDir . $newFileName;
        
        if (move_uploaded_file($tempPath, $destination)) {
            try {
                addDocument($_SESSION['user_id'], $libraryId, $fileName, $destination, $fileType, $fileSize);
                
                if ($libraryId) {
                    header("Location: library.php?id=$libraryId&uploaded=1");
                } else {
                    header("Location: index.php?uploaded=1");
                }
                exit;
            } catch (PDOException $e) {
                $error = 'Adatbázis hiba történt a fájl mentése közben: ' . htmlspecialchars($e->getMessage());
                error_log("File upload database error: " . $e->getMessage());
            }
        } else {
            $error = 'A fájl mentése sikertelen!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Új fájl feltöltése - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a>
      <?php if ($currentLibrary): ?>
        &gt; <a href="library.php?id=<?php echo $libraryId; ?>"><?php echo htmlspecialchars($currentLibrary['NAME']); ?></a>
      <?php endif; ?>
      &gt; <span>Új fájl feltöltése</span>
    </div>
    
    <h1>Új fájl feltöltése</h1>
    
    <?php if (!empty($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="uploaded_file">Válassz fájlt:</label>
          <input type="file" id="uploaded_file" name="uploaded_file" required>
        </div>
        
        <div class="form-group">
          <label for="library_id">Mappa:</label>
          <select id="library_id" name="library_id">
            <optgroup label="Saját mappák">
            <option value="">Gyökér mappa</option>
              <?php foreach ($libraries as $lib): ?>
                <?php if ($lib['type'] === 'own'): ?>
                  <option value="<?php echo $lib['LIBRARY_ID']; ?>" <?php echo ($libraryId == $lib['LIBRARY_ID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($lib['NAME']); ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </optgroup>
            
            <?php if (isset($lib['type']) && $lib['type'] === 'shared'): ?>
              <optgroup label="Velem megosztott mappák">
                <?php foreach ($libraries as $lib): ?>
                  <?php if ($lib['type'] === 'shared'): ?>
                    <option value="<?php echo $lib['LIBRARY_ID']; ?>" <?php echo ($libraryId == $lib['LIBRARY_ID']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($lib['NAME']); ?> (<?php echo htmlspecialchars($lib['OWNER_NAME']); ?>)
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </optgroup>
            <?php endif; ?>
          </select>
        </div>
        
        <div class="form-actions">
          <a href="<?php echo $libraryId ? 'library.php?id=' . $libraryId : 'index.php'; ?>" class="btn-secondary">Mégse</a>
          <button type="submit" class="btn-primary">Feltöltés</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>