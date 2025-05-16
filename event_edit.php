<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$eventID = (int) $_GET['id'];
$event = getEvent($eventID);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_time = $_POST['start'] ?? '';
    $end_time = $_POST['end'] ?? '';
    $location = $_POST['location'] ?? '';

    if (empty($title)) {
        $message = "<div class='error-message'>Név megadása kötelező!</div>";
    } elseif (empty($start_time)) {
        $message = "<div class='error-message'>Kezdés idejének megadása kötelező!</div>";
    } elseif (empty($end_time)) {
        $message = "<div class='error-message'>Befejezés idejének megadása kötelező!</div>";
    } elseif (isset($_GET['error'])) {
        $message = "<div class='error-message'>A végdátum nem lehet korábban, mint a kezdődátum!</div>";
    } else {
        edit_eventt($eventID, $title, $description, $start_time, $end_time, $location);
        $message = "<div class='success-message'>Esemény sikeresen szerkesztve!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esemény szerkesztése - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
        <h1>Esemény szerkesztése</h1>
        <?php if (!empty($message)): ?>
            <div ><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="form-container">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">Esemény neve:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($event["TITLE"]); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="description">Esemény leírása:</label>
                    <?php if (is_resource($event['DESCRIPTION'])) {
                        $event['DESCRIPTION'] = stream_get_contents($event['DESCRIPTION']);
                    } ?>
                    <input type="text" id="description" name="description"
                        value="<?php echo htmlspecialchars($event["DESCRIPTION"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Esemény helye:</label>
                    <input type="text" id="location" name="location"
                        value="<?php echo htmlspecialchars($event["LOCATION"]); ?>" required>
                </div>
                <div class="form-group">
                    <label for="start">Kezdés:</label><?php
                    $dt1 = DateTime::createFromFormat('d-M-y h.i.s.u A', $event['START_TIME']);
                    $formatted1 = $dt1->format('Y-m-d\TH:i'); ?>
                    <input type="datetime-local" id="start" name="start"
                        value="<?php echo htmlspecialchars($formatted1); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end">Vége:</label><?php
                    $dt = DateTime::createFromFormat('d-M-y h.i.s.u A', $event['END_TIME']);
                    $formatted = $dt->format('Y-m-d\TH:i'); ?>
                    <input type="datetime-local" id="end" name="end" value="<?php echo htmlspecialchars($formatted); ?>"
                        required>
                </div>
                <div class="form-actions">
                    <a href="<?php echo 'calendar.php'; ?>" class="btn-secondary">Mégse</a>

                    <button type="submit" class="btn-primary">Szerkesztés mentése</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>