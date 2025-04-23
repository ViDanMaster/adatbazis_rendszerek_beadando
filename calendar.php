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
    <title>Naptáraim - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <style>
        .fc .fc-toolbar.fc-header-toolbar {
            background-color: blue;
            color: white;
            padding: 10px;
            border-radius: 8px;
            height: 50px;
        }

        .fc .fc-day-today {
            background-color: blue !important;
        }

        .fc .fc-col-header-cell {
            background-color: blue;
            font-weight: bold;
        }

        .fc .fc-day-number {
            color: blue !important;
            font-weight: bold;
        }

        .fc .fc-button:hover {
            background-color: blue;
        }

        .fc .fc-button {
            background-color: blue;
            border: none;
            color: white;
            height: 20px;
            display: flex;
            justify-content: center;
            flex-direction: row;
            align-items: center;
            margin-top: 5px;
        }

        a {
            color: black;
        }
    </style>
</head>

<div>

    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
        <h1>Naptáraim</h1>
            <div class="drive-container">
                <?php
                include 'functions.php';
                if (!isset($conn) || $conn === null) {
                    die("<p style='color:#ea4335;background-color:#fce8e6;padding:12px;border-radius:4px;'>Adatbázis kapcsolati hiba!</p>");
                }
                try {
                    $calendars = getCalendar($_SESSION['user_id']);
                    
                    $hasContent = false;
                    
                    if (!empty($calendars)) {
                        $hasContent = true;
                        echo "<div class='files-grid'>";
                        foreach ($calendars as $lib) {
                            echo "<div class='item folder-item' data-id='{$lib['CALENDAR_ID']}'>";
                            echo "<div class='item-details'>";
                            echo "<div class='item-name'>" . htmlspecialchars($lib['NAME']) . "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    if (!$hasContent) {
                        echo "<div class='empty-state'><p>Nincsen megjeníthető naptár!</p></div>";
                    }
                } catch (PDOException $e) {
                    error_log("Adatbázis hiba: " . $e->getMessage());
                    echo "<div class='error-message'><p>Hiba történt az adatok lekérdezése közben.</p></div>";
                }
                ?>
                <div class="calendar"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const calendarEl = document.querySelector('.calendar');
                        const calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            locale: 'hu',
                            firstDay: 1,
                            events: []
                        });
                        calendar.render();
                    });
                </script>
            </div>
        </div>


        </body>

</html>