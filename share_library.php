<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$libraryId = (int)$_GET['id'];
$library = getLibraryById($libraryId);

if (!$library || $library['USER_ID'] != $_SESSION['user_id']) {
    header('Location: index.php');
    exit;
}

$message = '';
$success = false;

$currentShares = getLibraryShares($libraryId);

$availableUsers = getAllUsersExcept($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['share'])) {
        $sharedUserId = (int)$_POST['user_id'];
        $permission = $_POST['permission'];
        
        if ($sharedUserId && in_array($permission, ['read', 'edit'])) {
            if (shareLibrary($libraryId, $sharedUserId, $permission)) {
                $message = '<div class="success-message">Mappa sikeresen megosztva!</div>';
                $success = true;
                $currentShares = getLibraryShares($libraryId);
            } else {
                $message = '<div class="error-message">Hiba történt a megosztás során!</div>';
            }
        } else {
            $message = '<div class="error-message">Hiányzó vagy érvénytelen adatok!</div>';
        }
    } elseif (isset($_POST['remove_share'])) {
        $userId = (int)$_POST['user_id'];
        
        if (removeLibraryShare($libraryId, $userId)) {
            $message = '<div class="success-message">Megosztás sikeresen eltávolítva!</div>';
            $success = true;
            $currentShares = getLibraryShares($libraryId);
        } else {
            $message = '<div class="error-message">Hiba történt a megosztás eltávolítása során!</div>';
        }
    } elseif (isset($_POST['edit_share'])) {
        $userId = (int)$_POST['user_id'];
        $newPermission = $_POST['new_permission'];
        
        if (in_array($newPermission, ['read', 'edit'])) {
            if (shareLibrary($libraryId, $userId, $newPermission)) {
                $message = '<div class="success-message">Jogosultság sikeresen módosítva!</div>';
                $success = true;
                $currentShares = getLibraryShares($libraryId);
            } else {
                $message = '<div class="error-message">Hiba történt a jogosultság módosítása során!</div>';
            }
        } else {
            $message = '<div class="error-message">Érvénytelen jogosultság!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mappa megosztása - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a>
      &gt; <a href="library.php?id=<?php echo $libraryId; ?>"><?php echo htmlspecialchars($library['NAME']); ?></a>
      &gt; <span>Megosztás</span>
    </div>
    
    <h1>Mappa megosztása: <?php echo htmlspecialchars($library['NAME']); ?></h1>
    
    <?php echo $message; ?>
    
    <div class="form-container">
      <form method="post" action="">
        <div class="form-group">
          <label for="user_id">Felhasználó:</label>
          <select id="user_id" name="user_id" required>
            <option value="">-- Válassz felhasználót --</option>
            <?php foreach ($availableUsers as $user): ?>
              <option value="<?php echo $user['USER_ID']; ?>">
                <?php echo htmlspecialchars($user['USERNAME']); ?> (<?php echo htmlspecialchars($user['EMAIL']); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="permission">Jogosultság:</label>
          <select id="permission" name="permission" required>
            <option value="read">Olvasás</option>
            <option value="edit">Szerkesztés</option>
          </select>
        </div>
        
        <div class="form-actions">
          <a href="library.php?id=<?php echo $libraryId; ?>" class="btn-secondary">Mégse</a>
          <button type="submit" name="share" class="btn-primary">Megosztás</button>
        </div>
      </form>
      
      <?php if (!empty($currentShares)): ?>
        <h2>Jelenlegi megosztások</h2>
        <table class="share-table">
          <thead>
            <tr>
              <th>Felhasználó</th>
              <th>E-mail</th>
              <th>Jogosultság</th>
              <th>Műveletek</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($currentShares as $share): ?>
              <tr>
                <td><?php echo htmlspecialchars($share['USERNAME']); ?></td>
                <td><?php echo htmlspecialchars($share['EMAIL']); ?></td>
                <td>
                  <span class="permission-<?php echo $share['PERMISSION']; ?>">
                    <?php echo $share['PERMISSION'] === 'read' ? 'Olvasás' : 'Szerkesztés'; ?>
                  </span>
                </td>
                <td>
                  <div class="button-group">
                    <form method="post" class="edit-form" style="display: inline; margin-right: 8px;">
                      <input type="hidden" name="user_id" value="<?php echo $share['USER_ID']; ?>">
                      <select name="new_permission" class="permission-select" style="display: none;">
                        <option value="read" <?php echo $share['PERMISSION'] === 'read' ? 'selected' : ''; ?>>Olvasás</option>
                        <option value="edit" <?php echo $share['PERMISSION'] === 'edit' ? 'selected' : ''; ?>>Szerkesztés</option>
                      </select>
                      <button type="button" class="edit-btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">Módosítás</button>
                      <button type="submit" name="edit_share" class="save-btn btn-primary" style="padding: 4px 8px; font-size: 12px; display: none;">Mentés</button>
                      <button type="button" class="cancel-btn btn-secondary" style="padding: 4px 8px; font-size: 12px; display: none;">Mégse</button>
                    </form>
                    
                    <form method="post" style="display: inline;" onsubmit="return confirm('Biztosan el szeretnéd távolítani ezt a megosztást?');">
                      <input type="hidden" name="user_id" value="<?php echo $share['USER_ID']; ?>">
                      <button type="submit" name="remove_share" class="btn-secondary" style="padding: 4px 8px; font-size: 12px;">Eltávolítás</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const form = this.closest('form');
        const permSelect = form.querySelector('.permission-select');
        const saveBtn = form.querySelector('.save-btn');
        const cancelBtn = form.querySelector('.cancel-btn');
        
        this.style.display = 'none';
        permSelect.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';
        
        permSelect.dataset.original = permSelect.value;
      });
    });
    
    document.querySelectorAll('.cancel-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const form = this.closest('form');
        const permSelect = form.querySelector('.permission-select');
        const editBtn = form.querySelector('.edit-btn');
        const saveBtn = form.querySelector('.save-btn');
        
        permSelect.value = permSelect.dataset.original;
        
        editBtn.style.display = 'inline-block';
        permSelect.style.display = 'none';
        saveBtn.style.display = 'none';
        this.style.display = 'none';
      });
    });
  </script>

</body>
</html>