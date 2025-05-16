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
        }?>
        <div class="alma"><?php
        echo "<div class='item-name'><h1>" . htmlspecialchars($event['TITLE']) . "</h1></div>";?>
        <a href="event_edit.php?id=<?php echo $eventID; ?>" class="btn-secondary">Szerkesztés</a>
        </div>
        <div class="document-content"><?php
        echo "<div class='item-name'><p> Leírása: " . htmlspecialchars($event['DESCRIPTION']) . "</p></div>";
        echo "<div class='item-name'><p>Helyszín: " . htmlspecialchars($event['LOCATION']) . "</p></div>";
        echo "<div class='item-name'><p>Kezdete: </p></div>";
             $date = DateTime::createFromFormat('d-M-y h.i A', $event['START_TIME']);
            if ($date) {
                echo $date->format('Y-m-d H:i');  
            } else {
                echo "Hibás formátum";
            }
        echo "<div class='item-name'><p>Vége: </p></div>";
            $date = DateTime::createFromFormat('d-M-y h.i A', $event['END_TIME']);
            if ($date) {
                echo $date->format('Y-m-d H:i');  
            } else {
                echo "Hibás formátum";
            }
          ?>
          </div>
    </div>
</body>

</html>