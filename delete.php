<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id']) && (!isset($_GET['type']) || $_GET['type'] === 'document')) {
    $documentId = (int)$_GET['id'];
    $result = deleteDocument($documentId);
    
    $redirectUrl = 'index.php';
    if (isset($_GET['library_id'])) {
        $redirectUrl = 'library.php?id=' . (int)$_GET['library_id'];
    }
    
    header("Location: $redirectUrl");
    exit;
}

if (isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] === 'library') {
    $libraryId = (int)$_GET['id'];
    $library = getLibraryById($libraryId);
    
    if (!$library) {
        header('Location: index.php');
        exit;
    }
    
    $permission = getUserLibraryPermission($_SESSION['user_id'], $libraryId);
    if ($permission !== 'owner') {
        header('Location: index.php');
        exit;
    }
    
    try {
        deleteLibraryRecursive($libraryId);
        
        $returnId = isset($_GET['return']) ? (int)$_GET['return'] : null;
        if ($returnId) {
            header("Location: library.php?id=$returnId");
        } else {
            header("Location: index.php");
        }
        exit;
    } catch (Exception $e) {
        error_log("Error deleting library: " . $e->getMessage());
        die("Error deleting library. See error log for details.");
    }
}
?>