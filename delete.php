<?php
session_start();
include 'functions.php';

if (!isset($_GET['type']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$type = $_GET['type'];
$id = (int)$_GET['id'];

if ($type !== 'library' && $type !== 'document') {
    header('Location: index.php');
    exit;
}

try {
    if ($type === 'library') {
        deleteDocumentsInLibrary($id);
        
        deleteLibrary($id);
        
        header('Location: index.php?deleted=library');
        exit;
    }
    
    if ($type === 'document') {
        $libraryId = getDocumentLibraryId($id);
        
        deleteDocument($id);
        
        $redirectUrl = $libraryId 
            ? "library.php?id={$libraryId}&deleted=document" 
            : "index.php?deleted=document";
            
        header("Location: $redirectUrl");
        exit;
    }
} catch (PDOException $e) {
    error_log("Adatbázis hiba: " . $e->getMessage());
    header('Location: index.php?error=delete');
    exit;
}