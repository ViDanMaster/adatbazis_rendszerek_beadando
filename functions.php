<?php
$tns = "
(DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))
    (CONNECT_DATA = (SERVICE_NAME = XE))
)";
$db_username = "system";
$db_password = "oracle";

try {
    $conn = new PDO("oci:dbname=" . $tns, $db_username, $db_password);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function addLibrary($user_id, $name) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Libraries (library_id, user_id, name) VALUES (LIBRARY_SEQ.NEXTVAL, :user_id, :name)");
    $stmt->execute([':user_id' => $user_id, ':name' => $name]);
}

function updateLibrary($library_id, $name) {
    global $conn;
    $stmt = $conn->prepare("UPDATE Libraries SET name = :name WHERE library_id = :library_id");
    $stmt->execute([':name' => $name, ':library_id' => $library_id]);
}

function deleteLibrary($library_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM Libraries WHERE library_id = :library_id");
    $stmt->execute([':library_id' => $library_id]);
}

function addDocument($user_id, $library_id, $name, $file_path, $file_type, $file_size) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Documents (document_id, user_id, library_id, name, file_path, file_type, file_size, created_at, updated_at) 
                           VALUES (DOCUMENT_SEQ.NEXTVAL, :user_id, :library_id, :name, :file_path, :file_type, :file_size, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $stmt->execute([
        ':user_id' => $user_id, 
        ':library_id' => $library_id, 
        ':name' => $name,
        ':file_path' => $file_path,
        ':file_type' => $file_type,
        ':file_size' => $file_size
    ]);
}

function updateDocument($document_id, $name, $library_id = null) {
    global $conn;
    $sql = "UPDATE Documents SET name = :name, updated_at = CURRENT_TIMESTAMP";
    $params = [':name' => $name, ':document_id' => $document_id];
    
    if ($library_id !== null) {
        $sql .= ", library_id = :library_id";
        $params[':library_id'] = $library_id;
    }
    
    $sql .= " WHERE document_id = :document_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
}

function deleteDocument($document_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM Documents WHERE document_id = :document_id");
    $stmt->execute([':document_id' => $document_id]);
}

function getDocumentById($documentId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT d.*, l.name as library_name 
                               FROM Documents d 
                               LEFT JOIN Libraries l ON d.library_id = l.library_id 
                               WHERE d.document_id = :id");
        $stmt->execute([':id' => $documentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentum lekérésekor: " . $e->getMessage());
        return false;
    }
}

function authenticateUser($username, $password) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT user_id, username, password FROM Users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Felhasználó nem található!'];
        } elseif ($password === $user['PASSWORD']) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Érvénytelen jelszó!'];
        }
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a bejelentkezés során: " . $e->getMessage());
        return ['success' => false, 'message' => 'Adatbázis hiba történt!'];
    }
}

function registerUser($username, $password, $email, $name) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'A felhasználónév már foglalt!'];
        }
        
        $stmt = $conn->prepare("INSERT INTO Users (user_id, username, password, email, name, created_date) 
                               VALUES (USER_SEQ.NEXTVAL, :username, :password, :email, :name, SYSDATE)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':name' => $name
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a regisztráció során: " . $e->getMessage());
        return ['success' => false, 'message' => 'Adatbázis hiba történt!'];
    }
}

function getLibraries() {
    global $conn;
    
    try {
        return $conn->query("SELECT * FROM Libraries ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a mappák lekérésekor: " . $e->getMessage());
        return [];
    }
}

function getLibraryById($libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM Libraries WHERE library_id = :id");
        $stmt->execute([':id' => $libraryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a mappa lekérésekor: " . $e->getMessage());
        return false;
    }
}

function getDocumentsByLibraryId($libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM Documents WHERE library_id = :library_id ORDER BY name");
        $stmt->execute([':library_id' => $libraryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentumok lekérésekor: " . $e->getMessage());
        return [];
    }
}

function getRootDocuments() {
    global $conn;
    
    try {
        return $conn->query("SELECT * FROM Documents WHERE library_id IS NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a gyökér dokumentumok lekérésekor: " . $e->getMessage());
        return [];
    }
}

function updateDocumentContent($documentId, $name, $content = null, $libraryId = null) {
    global $conn;
    
    try {
        $sql = "UPDATE Documents SET name = :name, updated_at = CURRENT_TIMESTAMP";
        $params = [
            ':name' => $name,
            ':id' => $documentId
        ];
        
        if ($content !== null) {
            $sql .= ", file_path = :content";
            $params[':content'] = $content;
        }
        
        if ($libraryId !== null) {
            $sql .= ", library_id = :library_id";
            $params[':library_id'] = $libraryId;
        }
        
        $sql .= " WHERE document_id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return true;
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentum frissítésekor: " . $e->getMessage());
        return false;
    }
}

function deleteDocumentsInLibrary($libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM Documents WHERE library_id = :id");
        $stmt->execute([':id' => $libraryId]);
        return true;
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentumok törlésekor: " . $e->getMessage());
        return false;
    }
}

function getDocumentLibraryId($documentId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT library_id FROM Documents WHERE document_id = :id");
        $stmt->execute([':id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['LIBRARY_ID'] : null;
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentum mappájának lekérésekor: " . $e->getMessage());
        return null;
    }
}
?>