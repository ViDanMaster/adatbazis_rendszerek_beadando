<?php
session_start();

include 'functions.php';
if (!isset($conn) || $conn === null) {
    die("<p style='color:#ea4335;background-color:#fce8e6;padding:12px;border-radius:4px;'>Adatbázis kapcsolati hiba!</p>");
}
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$eventID = (int)$_GET['id'];
$event = getEvent($eventID);

if (!$event) {
    header('Location: index.php');
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
        if (is_resource($event['DESCRIPTION'])) {
            $event['DESCRIPTION'] = stream_get_contents($event['DESCRIPTION']);
        }
      echo "<div class='item-name'><h1>" . htmlspecialchars($event['TITLE']) . "</h1></div>";
      echo "<div class='item-name'><p> Leírása: " . htmlspecialchars($event['DESCRIPTION']) . "</p></div>";
      echo "<div class='item-name'><p>Helyszín: " . htmlspecialchars($event['LOCATION']) . "</p></div>";
       echo "<div class='item-name'><p>Kezdete: " . htmlspecialchars((string)$event['START_TIME']) . "</p></div>";
      echo "<div class='item-name'><p>Vége: " . htmlspecialchars((string)$event['END_TIME']) . "</p></div>";


      

          ?>
    </div>
</body>

</html>