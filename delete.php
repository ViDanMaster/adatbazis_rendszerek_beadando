<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check user permissions for the item being deleted
if (isset($_GET['type']) && $_GET['type'] == 'library' && isset($_GET['id'])) {
    $libraryId = (int)$_GET['id'];
    $userPermission = getUserLibraryPermission($_SESSION['user_id'], $libraryId);
    
    if ($userPermission !== 'owner' && $userPermission !== 'edit') {
        $_SESSION['error'] = "Nincs jogosultságod a törléshez!";
        header('Location: index.php');
        exit;
    }
} else if (isset($_GET['type']) && $_GET['type'] == 'document' && isset($_GET['id'])) {
    $docId = (int)$_GET['id'];
    $document = getDocumentById($docId);
    
    if ($document && $document['LIBRARY_ID']) {
        $userPermission = getUserLibraryPermission($_SESSION['user_id'], $document['LIBRARY_ID']);
        
        if ($document['USER_ID'] != $_SESSION['user_id'] && 
            ($userPermission !== 'owner' && $userPermission !== 'edit')) {
            $_SESSION['error'] = "Nincs jogosultságod a törléshez!";
            header('Location: index.php');
            exit;
        }
    }
}

if (isset($_GET['id']) && (!isset($_GET['type']) || $_GET['type'] === 'event')) {
    $eventId = (int)$_GET['id'];
    $result = deleteEvent($eventId);
    
    $redirectUrl = 'index.php';
    header("Location: $redirectUrl");
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