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
            background-color: #6EC1F4;
            color: white;
        }

        .fc .fc-day-today {
            background-color: #6EC1F4 !important;
        }

        .fc .fc-col-header-cell {
            background-color: #6EC1F4;
            font-weight: bold;
        }

        .fc .fc-day-number {
            color: #6EC1F4 !important;
            font-weight: bold;
        }

        .fc .fc-button:hover {
            background-color: #6EC1F4;
        }

        .fc .fc-button {
            background-color: #6EC1F4;
            border: none;
            color: white;
            display: flex;
            justify-content: center;
            flex-direction: row;
            align-items: center;
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
               ?>
                <div class="calendar"></div>
                <script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.querySelector('.calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'hu',
        firstDay: 1,
        events: 'fetch_events.php', // Itt történik a lekérés
        eventClick: function(info) {
            alert('Esemény címe: ' + info.event.title);
        }
    });
    calendar.render();
});
</script>
            </div>
        </div>


        </body>

</html>