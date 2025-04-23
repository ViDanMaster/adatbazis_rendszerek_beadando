<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$sharedLibraries = getSharedLibraries($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Velem megosztva - Goofle</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <h1>Velem megosztva</h1>

    <div class="drive-container">
      <?php
      if (empty($sharedLibraries)) {
        echo "<div class='empty-state'><p>Még nincsenek veled megosztott mappák.</p></div>";
      } else {
        echo "<div class='files-grid'>";
        foreach ($sharedLibraries as $lib) {
          echo "<div class='item folder-item' data-id='{$lib['LIBRARY_ID']}'>";
          echo "<div class='item-icon'>📁</div>";
          echo "<div class='item-details'>";
          echo "<div class='item-name'>" . htmlspecialchars($lib['NAME']) . "</div>";
          echo "<div class='shared-by'>Megosztotta: " . htmlspecialchars($lib['SHARED_BY']) . "</div>";
          echo "<div class='permission-badge permission-{$lib['PERMISSION']}'>";
          echo $lib['PERMISSION'] === 'read' ? 'Olvasás' : 'Szerkesztés';
          echo "</div>";
          echo "</div>";
          echo "</div>";
        }
        echo "</div>";
      }
      ?>
    </div>
  </div>

  <script>
    document.querySelectorAll('.folder-item').forEach(folder => {
      folder.addEventListener('click', () => {
        const folderId = folder.getAttribute('data-id');
        window.location.href = `library.php?id=${folderId}`;
      });
    });
  </script>

</body>
</html>