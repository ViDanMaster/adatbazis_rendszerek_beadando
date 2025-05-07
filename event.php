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
    <title>Esemény megtekintése - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
    <?php
      include 'functions.php';
      if (!isset($conn) || $conn === null) {
          die("<p style='color:#ea4335;background-color:#fce8e6;padding:12px;border-radius:4px;'>Adatbázis kapcsolati hiba!</p>");
      }

      try {
          $esemenyek = getEvent($_SESSION['user_id']);
          
          $hasContent = false;
          
          if (!empty($esemenyek)) {
              $hasContent = true;
              echo "<div class='section-header'>Mappák</div>";
              echo "<div class='files-grid'>";
              foreach ($esemenyek as $lib) {
                  echo "<div class='item folder-item' data-id='{$lib['LIBRARY_ID']}'>";
                  echo "<div class='item-icon'>📁</div>";
                  echo "<div class='item-details'>";
                  echo "<div class='item-name'>" . htmlspecialchars($lib['NAME']) . "</div>";
                  echo "</div>";
                  echo "</div>";
              }
              echo "</div>";
          }}
          ?>
    </div>
</body>

</html>