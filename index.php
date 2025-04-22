<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Google Drive Klón</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <h1>Saját fájlok</h1>

    <div class="drive-container">
      <?php
      include 'functions.php';
      if (!isset($conn) || $conn === null) {
          die("<p style='color:#ea4335;background-color:#fce8e6;padding:12px;border-radius:4px;'>Adatbázis kapcsolati hiba!</p>");
      }

      try {
          $libraries = getRootLibraries($_SESSION['user_id']);
          
          $hasContent = false;
          
          if (!empty($libraries)) {
              $hasContent = true;
              echo "<div class='section-header'>Mappák</div>";
              echo "<div class='files-grid'>";
              foreach ($libraries as $lib) {
                  echo "<div class='item folder-item' data-id='{$lib['LIBRARY_ID']}'>";
                  echo "<div class='item-icon'>📁</div>";
                  echo "<div class='item-details'>";
                  echo "<div class='item-name'>" . htmlspecialchars($lib['NAME']) . "</div>";
                  echo "</div>";
                  echo "</div>";
              }
              echo "</div>";
          }
              
          $rootDocs = getRootDocuments($_SESSION['user_id']);
          if (!empty($rootDocs)) {
              $hasContent = true;
              echo "<div class='section-header'>Fájlok</div>";
              echo "<div class='files-grid'>";
              foreach ($rootDocs as $doc) {
                  echo "<div class='item document-item' data-id='{$doc['DOCUMENT_ID']}'>";
                  echo "<div class='item-icon'>📄</div>";
                  echo "<div class='item-details'>";
                  echo "<div class='item-name'>" . htmlspecialchars($doc['NAME']) . "</div>";
                  echo "</div>";
                  echo "</div>";
              }
              echo "</div>";
          }
          
          if (!$hasContent) {
              echo "<div class='empty-state'><p>Még nincsenek mappák vagy fájlok. Hozz létre egy új mappát vagy tölts fel egy fájlt!</p></div>";
          }
      } catch (PDOException $e) {
          error_log("Adatbázis hiba: " . $e->getMessage());
          echo "<div class='error-message'><p>Hiba történt az adatok lekérdezése közben.</p></div>";
      }
      ?>
    </div>
  </div>

  <div id="context-menu" class="context-menu">
    <ul>
      <li id="open-item"><i class="menu-icon">📂</i>Megnyitás</li>
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
      if (item.classList.contains('document-item')) {
        shareOption.style.display = 'none';
      } else {
        shareOption.style.display = 'flex';
      }
      
      contextMenu.classList.add('active');
    }

    document.querySelectorAll('.folder-item, .document-item').forEach(item => {
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
        const id = targetItem.getAttribute('data-id');
        if (targetItem.classList.contains('folder-item')) {
          window.location.href = `edit_library.php?id=${id}`;
        } else {
          window.location.href = `edit_document.php?id=${id}`;
        }
      }
    });

    document.getElementById('share-item').addEventListener('click', () => {
      if (targetItem && targetItem.classList.contains('folder-item')) {
        const folderId = targetItem.getAttribute('data-id');
        window.location.href = `share_library.php?id=${folderId}`;
      }
    });

    document.getElementById('delete-item').addEventListener('click', () => {
      if (targetItem) {
        const id = targetItem.getAttribute('data-id');
        const type = targetItem.classList.contains('folder-item') ? 'library' : 'document';
        const confirmMessage = type === 'library' ? 
          'Biztosan törlöd ezt a mappát és annak tartalmát?' : 
          'Biztosan törlöd ezt a dokumentumot?';
          
        if (confirm(confirmMessage)) {
          window.location.href = `delete.php?type=${type}&id=${id}`;
        }
      }
    });
  </script>

</body>
</html>