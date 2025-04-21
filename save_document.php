<?php
session_start();
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$libraryId = isset($_POST['library_id']) && !empty($_POST['library_id']) ? (int)$_POST['library_id'] : null;

if (!isset($_FILES['uploaded_file']) || $_FILES['uploaded_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: add_document.php?error=upload_failed');
    exit;
}

$file = $_FILES['uploaded_file'];
$fileName = $file['name'];
$fileTmpPath = $file['tmp_name'];
$fileSize = $file['size'];
$fileType = $file['type'];

$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$newFileName = uniqid('', true) . '.' . $fileExt;
$uploadPath = $uploadDir . $newFileName;

if (move_uploaded_file($fileTmpPath, $uploadPath)) {
    try {
        addDocument(
            $_SESSION['user_id'], 
            $libraryId, 
            $fileName, 
            $uploadPath, 
            $fileType, 
            $fileSize
        );
        
        if ($libraryId) {
            header("Location: library.php?id=$libraryId&success=1");
        } else {
            header("Location: index.php?success=1");
        }
        exit;
        
    } catch (PDOException $e) {
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        error_log("Adatbázis hiba dokumentum feltöltésekor: " . $e->getMessage());
        header('Location: add_document.php?error=database');
        exit;
    } catch (Exception $e) {
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        error_log("Szerver hiba dokumentum feltöltésekor: " . $e->getMessage());
        header('Location: add_document.php?error=server');
        exit;
    }
} else {
    header('Location: add_document.php?error=upload_failed');
    exit;
}
?>