<?php
session_start();
require_once 'functions.php';

// Ellenőrizzük, hogy van-e érvényes ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$libraryId = (int)$_GET['id'];

// Mappa adatainak lekérése
$library = getLibraryById($libraryId);

if (!$library) {
    header('Location: index.php');
    exit;
}

// Dokumentumok lekérése
$documents = getDocumentsByLibraryId($libraryId);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($library['NAME']); ?> - Drive Klón</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a> &gt; <span><?php echo htmlspecialchars($library['NAME']); ?></span>
    </div>
    
    <div class="library-header">
      <h1><?php echo htmlspecialchars($library['NAME']); ?></h1>
      <div class="library-actions">
        <a href="add_document.php?library_id=<?php echo $libraryId; ?>" class="btn-primary">Új fájl</a>
        <a href="edit_library.php?id=<?php echo $libraryId; ?>" class="btn-secondary">Szerkesztés</a>
      </div>
    </div>

    <div class="drive-container">
      <?php if (empty($documents)): ?>
        <div class="empty-state">
          <p>Ez a mappa még üres. Hozz létre új fájlokat a tároláshoz!</p>
        </div>
      <?php else: ?>
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
    </div>
  </div>

  <!-- Kontextus menü -->
  <div id="context-menu" class="context-menu">
    <ul>
      <li id="open-item"><i class="menu-icon">📄</i>Megnyitás</li>
      <li id="edit-item"><i class="menu-icon">✏️</i>Szerkesztés</li>
      <li id="delete-item"><i class="menu-icon">🗑️</i>Törlés</li>
    </ul>
  </div>

  <script>
    // Elem megnyitása kattintásra
    document.querySelectorAll('.document-item').forEach(doc => {
      doc.addEventListener('click', () => {
        const docId = doc.getAttribute('data-id');
        window.location.href = `view_document.php?id=${docId}`;
      });
    });

    // Kontextus menü
    const contextMenu = document.getElementById('context-menu');
    let targetItem = null;

    // Kontextus menü megjelenítése jobb klikk esetén
    function showContextMenu(e, item) {
      e.preventDefault();
      targetItem = item;
      
      // Pozícionálás
      contextMenu.style.left = `${e.pageX}px`;
      contextMenu.style.top = `${e.pageY}px`;
      
      contextMenu.classList.add('active');
    }

    document.querySelectorAll('.document-item').forEach(item => {
      item.addEventListener('contextmenu', (e) => {
        showContextMenu(e, item);
      });
    });

    document.addEventListener('click', () => {
      contextMenu.classList.remove('active');
    });

    document.getElementById('open-item').addEventListener('click', () => {
      if (targetItem) {
        const docId = targetItem.getAttribute('data-id');
        window.location.href = `view_document.php?id=${docId}`;
      }
    });

    document.getElementById('edit-item').addEventListener('click', () => {
      if (targetItem) {
        const docId = targetItem.getAttribute('data-id');
        window.location.href = `edit_document.php?id=${docId}`;
      }
    });

    document.getElementById('delete-item').addEventListener('click', () => {
      if (targetItem) {
        const docId = targetItem.getAttribute('data-id');
        if (confirm('Biztosan törölni szeretnéd ezt a dokumentumot?')) {
          window.location.href = `delete_document.php?id=${docId}&library_id=<?php echo $libraryId; ?>`;
        }
      }
    });
  </script>

</body>
</html>