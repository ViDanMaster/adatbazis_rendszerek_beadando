<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $libraries = getLibraries();
    echo json_encode($libraries);
} catch (PDOException $e) {
    error_log("Adatbázis hiba: " . $e->getMessage());
    echo json_encode(['error' => 'Hiba a mappák lekérésekor']);
}