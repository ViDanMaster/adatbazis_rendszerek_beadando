<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$libraryId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

$library = getLibraryById($libraryId);

if (!$library) {
    header('Location: index.php');
    exit;
}

$userPermission = getUserLibraryPermission($userId, $libraryId);

if ($userPermission === null) {
    header('Location: index.php');
    exit;
}

$isOwner = ($userPermission === 'owner');
$canEdit = ($userPermission === 'owner' || $userPermission === 'edit');
$canRead = true;

$parentLibraryId = getParentLibraryId($libraryId);
$parentLibrary = null;
if ($parentLibraryId) {
    $parentLibrary = getLibraryById($parentLibraryId);
}

$documents = getDocumentsByLibraryId($libraryId);
$subLibraries = getSubLibraries($libraryId);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($library['NAME']); ?> - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a>
      <?php if ($parentLibrary): ?>
        &gt; <a href="library.php?id=<?php echo $parentLibraryId; ?>"><?php echo htmlspecialchars($parentLibrary['NAME']); ?></a>
      <?php endif; ?>
      &gt; <span><?php echo htmlspecialchars($library['NAME']); ?></span>
    </div>
    
    <div class="library-header">
      <h1>
        <?php echo htmlspecialchars($library['NAME']); ?>
        <?php if (!$isOwner): ?>
          <span class="shared-badge">(<?php echo $userPermission === 'edit' ? 'Szerkeszthető' : 'Olvasható'; ?>)</span>
        <?php endif; ?>
      </h1>
      <div class="library-actions">
        <?php if ($canEdit): ?>
          <a href="add_document.php?library_id=<?php echo $libraryId; ?>" class="btn-primary">Új fájl</a>
          <?php if ($isOwner): ?>
            <a href="add_library.php?parent_id=<?php echo $libraryId; ?>" class="btn-primary">Új almappa</a>
            <a href="edit_library.php?id=<?php echo $libraryId; ?>" class="btn-secondary">Szerkesztés</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="drive-container">
      <?php if (empty($subLibraries) && empty($documents)): ?>
        <div class="empty-state">
          <p>Ez a mappa még üres. Hozz létre új mappákat vagy fájlokat a tároláshoz!</p>
        </div>
      <?php else: ?>
        <?php if (!empty($subLibraries)): ?>
          <div class="section-header">Almappák</div>
          <div class="files-grid">
            <?php foreach ($subLibraries as $subLib): ?>
              <div class="item folder-item" data-id="<?php echo $subLib['LIBRARY_ID']; ?>">
                <div class="item-icon">📁</div>
                <div class="item-details">
                  <div class="item-name"><?php echo htmlspecialchars($subLib['NAME']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($documents)): ?>
          <div class="section-header">Fájlok</div>
          <div class="files-grid">
            <?php foreach ($documents as $doc): ?>
              <div class="item document-item" data-id="<?php echo $doc['DOCUMENT_ID']; ?>">
                <div class="item-icon">📄</div>
                <div class="item-details">
                  <div class="item-name"><?php echo htmlspecialchars($doc['NAME']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <div id="context-menu" class="context-menu">
    <ul>
      <li id="open-item"><i class="menu-icon">📄</i>Megnyitás</li>
      <li id="edit-item"><i class="menu-icon">✏️</i>Szerkesztés</li>
      <li id="share-item"><i class="menu-icon">🔗</i>Megosztás</li>
      <li id="delete-item"><i class="menu-icon">🗑️</i>Törlés</li>
    </ul>
  </div>

  <script>
    document.querySelectorAll('.folder-item').forEach(folder => {
      folder.addEventListener('click', () => {
        const folderId = folder.getAttribute('data-id');
        window.location.href = `library.php?id=${folderId}`;
      });
    });

    document.querySelectorAll('.document-item').forEach(doc => {
      doc.addEventListener('click', () => {
        const docId = doc.getAttribute('data-id');
        window.location.href = `view_document.php?id=${docId}`;
      });
    });

    const contextMenu = document.getElementById('context-menu');
    let targetItem = null;

    function showContextMenu(e, item) {
      e.preventDefault();
      targetItem = item;
      
      contextMenu.style.left = `${e.pageX}px`;
      contextMenu.style.top = `${e.pageY}px`;
      
      const shareOption = document.getElementById('share-item');
      const editOption = document.getElementById('edit-item');
      const deleteOption = document.getElementById('delete-item');
      
     
      
      // Hide edit and delete options if user doesn't have edit permissions
      <?php if (!$canEdit): ?>
        editOption.style.display = 'none';
        deleteOption.style.display = 'none';
      <?php endif; ?>
      
      contextMenu.classList.add('active');
    }

    document.querySelectorAll('.document-item, .folder-item').forEach(item => {
      item.addEventListener('contextmenu', (e) => {
        showContextMenu(e, item);
      });
    });

    document.addEventListener('click', () => {
      contextMenu.classList.remove('active');
    });

    document.getElementById('open-item').addEventListener('click', () => {
      if (targetItem) {
        targetItem.click();
      }
    });

    document.getElementById('edit-item').addEventListener('click', () => {
      if (targetItem) {
        if (targetItem.classList.contains('folder-item')) {
          const folderId = targetItem.getAttribute('data-id');
          window.location.href = `edit_library.php?id=${folderId}`;
        } else {
          const docId = targetItem.getAttribute('data-id');
          window.location.href = `edit_document.php?id=${docId}`;
        }
      }
    });

    document.getElementById('share-item').addEventListener('click', () => {
      if (targetItem) {
        const folderId = targetItem.getAttribute('data-id');
        if (targetItem.classList.contains('folder-item')) {
        window.location.href = `share_library.php?id=${folderId}`;
        } else {
          window.location.href = `share_document.php?id=${folderId}`;
        }
      }
    });

    document.getElementById('delete-item').addEventListener('click', () => {
      if (targetItem) {
        <?php if ($canEdit): // Only allow deletion if user has edit permission ?>
          if (targetItem.classList.contains('folder-item')) {
            const folderId = targetItem.getAttribute('data-id');
            if (confirm('Biztosan törölni szeretnéd ezt a mappát és annak tartalmát?')) {
              window.location.href = `delete.php?type=library&id=${folderId}&return=<?php echo $libraryId; ?>`;
            }
          } else {
            const docId = targetItem.getAttribute('data-id');
            if (confirm('Biztosan törölni szeretnéd ezt a dokumentumot?')) {
              window.location.href = `delete.php?type=document&id=${docId}&library_id=<?php echo $libraryId; ?>`;
            }
          }
        <?php else: ?>
          alert('Nincs jogosultságod a törléshez!');
        <?php endif; ?>
      }
    });
  </script>

</body>
</html>