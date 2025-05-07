<p?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_time = $_POST['start'] ?? '';
    $end_time = $_POST['end'] ?? '';
    $location = $_POST['location'] ?? '';
    $is_recurring = $_POST['recurring'] ?? '';

    if (empty($title)) {
        $message = "<p class='error'>Név megadása kötelező!</p>";
    } elseif (empty($start_time)) {
        $message = "<p class='error'>Kezdés idejének megadása kötelező!</p>";
    } elseif (empty($end_time)) {
        $message = "<p class='error'>Befejezés idejének megadása kötelező!</p>";
    } else {
        addCalendar($_SESSION['user_id'], $name, $color);
        $message = "<p class='message'>Esemény sikeresen létrehozva!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új esemény létrehozása - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
        <h1>Új esemény létrehozása</h1>
        <?php if (!empty($message)): ?>
            <div class="error-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">Esemény neve:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Esemény leírása:</label>
                    <input type="text" id="description" name="description" required>
                </div>
                <div class="form-group">
                    <label for="location">Esemény helye:</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <label for="start">Kezdés:</label>
                    <input type="datetime-local" id="start" name="start" required>
                </div>
                <div class="form-group">
                    <label for="end">Vége:</label>
                    <input type="datetime-local" id="end" name="end" required>
                </div>
                <div class="form-group">
                    <p >Esemény ismétlődik?</p>
                        <input type="radio" id="recurrings" name="recurring_yes" value="TRUE">
                        <label class="iss" for="recurrings">Igen</label><br>
                        <input type="radio" id="recurringn" name="recurring_no" value="FALSE">
                        <label class="iss" for="recurringn">Nem</label><br>
                    <!--<input type="radio" id="recurring" name="recurring" required> -->
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Létrehozás</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>