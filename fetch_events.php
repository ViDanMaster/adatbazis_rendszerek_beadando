<?php
session_start();
include 'functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    // Itt formázzuk az időpontokat ISO 8601 formátumra (YYYY-MM-DD"T"HH24:MI:SS)
    $stmt = $conn->prepare("
        SELECT 
            EVENT_ID,
            TITLE,
            TO_CHAR(START_TIME, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS START_TIME,
            TO_CHAR(END_TIME, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS END_TIME
        FROM events
        WHERE USER_ID = :user_id
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $events = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => $row['EVENT_ID'],
            'title' => mb_convert_encoding($row['TITLE'], 'UTF-8', 'auto'),
            'start' => $row['START_TIME'],
            'end' => $row['END_TIME']
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Hiba: ' . $e->getMessage()]);
}
