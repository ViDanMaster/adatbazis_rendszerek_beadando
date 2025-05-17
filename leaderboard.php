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
    <title>Ranglétra - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/top_navbar.php'; ?>
<?php include 'components/sidebar_navbar.php'; ?>

<div class="main-content">
    <h1>Ranglétra</h1>

    <div class="drive-container">
        <?php
        include 'functions.php';
        if (!isset($conn) || $conn === null) {
            die("<p style='color:#ea4335;background-color:#fce8e6;padding:12px;border-radius:4px;'>Adatbázis kapcsolati hiba!</p>");
        }

        $avg= average_leaderboard();




        $users = event_leaderboard();
        $doc_counts= document_leaderboard();
        $lib_counts = library_leaderboard();
        $doc_shares = document_shares();
        $lib_shares = library_shares();
        $avg_docShares = avg_docShares() ?? 0;
        $avg_libShares = avg_libShares() ?? 0;

        if (count($users) === 0) {
            echo "<p>Nincs felhasználó vagy esemény az adatbázisban.</p>";
        } else {
            echo "<table border='1' cellpadding='10' cellspacing='0' id='leaderboard'>";
            echo "<tr><th colspan='5'>Átlagok</th></tr>";
            echo "<tr><th>Események átlaga</th><th>Dokumentumok átlaga</th><th>Mappák átlaga</th><th>Könyvtár megosztások átlaga</th><th>Dokumentum megosztások átlaga</th></tr>";
            echo "<tr><th>{$avg['AVG_EVENT_COUNT']}</th><th>{$avg['AVG_DOCUMENT_COUNT']}</th><th>{$avg['AVG_LIBRARY_COUNT']}</th><th>{$avg_libShares}</th><th>{$avg_docShares}</th></tr>";


            echo "<table border='1' cellpadding='10' cellspacing='0' id='leaderboard'>";
            echo "<br><tr><th>Felhasználónév</th><th>Események száma</th><th>Dokumentumok</th><th>Könyvtárak</th><th>Megosztások</th></tr>";

            foreach ($users as $user) {
                $username = $user['USERNAME'];
                $event_count = $user['EVENT_COUNT'];
                $doc_count= isset($doc_counts[$username]) ? $doc_counts[$username] : 0;
                $lib_count = isset($lib_counts[$username]) ? $lib_counts[$username] : 0;
                $docShare= isset($doc_shares[$username]) ? $doc_shares[$username] : 0;
                $libShare = isset($lib_shares[$username]) ? $lib_shares[$username] : 0;

                $userShares = $docShare+$libShare;

                if ($event_count == 0 && $doc_count == 0 && $lib_count == 0) {
                    continue;
                }

                echo "<tr>";
                echo "<td>" . htmlspecialchars($username) . "</td>";
                echo "<td>" . htmlspecialchars($event_count) . "</td>";
                echo "<td>" . htmlspecialchars($doc_count) . "</td>";
                echo "<td>" . htmlspecialchars($lib_count) . "</td>";
                echo "<td>" . htmlspecialchars($userShares) . "</td>";
                echo "</tr>";
            }

            echo "</table>";
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


</body>
</html>
