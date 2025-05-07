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

function getUserData($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
    
    if ($library_id === null) {
        $stmt = $conn->prepare("INSERT INTO Documents (document_id, user_id, library_id, name, file_path, file_type, file_size, created_at, updated_at) 
                               VALUES (DOCUMENT_SEQ.NEXTVAL, :user_id, NULL, :name, :file_path, :file_type, :file_size, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $name,
            ':file_path' => $file_path,
            ':file_type' => $file_type,
            ':file_size' => $file_size
        ]);
    } else {
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
}

function deleteDocument($document_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT file_path FROM Documents WHERE document_id = :document_id");
        $stmt->execute([':document_id' => $document_id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("DELETE FROM Documents WHERE document_id = :document_id");
        $stmt->execute([':document_id' => $document_id]);
        
        if ($file && isset($file['FILE_PATH'])) {
            $filePath = $file['FILE_PATH'];
            if (file_exists($filePath)) {
                unlink($filePath);
                return ['success' => true, 'message' => 'Document and file deleted successfully'];
            } else {
                error_log("File not found while deleting document: " . $filePath);
                return ['success' => true, 'message' => 'Document deleted, but file was not found on disk'];
            }
        }
        
        return ['success' => true, 'message' => 'Document deleted successfully'];
    } catch (PDOException $e) {
        error_log("Database error deleting document: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error while deleting document'];
    } catch (Exception $e) {
        error_log("Error deleting file: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error deleting physical file'];
    }
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
        } elseif (password_verify($password, $user['PASSWORD'])) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Érvénytelen jelszó!'];
        }
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a bejelentkezés során: " . $e->getMessage());
        return ['success' => false, 'message' => 'Adatbázis hiba történt!'];
    }
}

function registerUser($username, $password, $email) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'A felhasználónév már foglalt!'];
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Users (user_id, username, password, email) 
                               VALUES (USER_SEQ.NEXTVAL, :username, :password, :email)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email' => $email
        ]);
        $stmt = $conn->prepare("SELECT user_id, username FROM Users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'user' => $user];
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a regisztráció során: " . $e->getMessage());
        return ['success' => false, 'message' => 'Adatbázis hiba történt: ' . $e->getMessage()];
    }
}

function getLibraries($user_id = null) {
    global $conn;
    
    try {
        if ($user_id !== null) {
            $stmt = $conn->prepare("SELECT * FROM Libraries WHERE user_id = :user_id ORDER BY name");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $conn->query("SELECT * FROM Libraries ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
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

function getRootDocuments($user_id = null) {
    global $conn;
    
    try {
        if ($user_id !== null) {
            $stmt = $conn->prepare("SELECT * FROM Documents WHERE library_id IS NULL AND user_id = :user_id ORDER BY name");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $conn->query("SELECT * FROM Documents WHERE library_id IS NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
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
        $stmt = $conn->prepare("SELECT document_id, file_path FROM Documents WHERE library_id = :id");
        $stmt->execute([':id' => $libraryId]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("DELETE FROM Documents WHERE library_id = :id");
        $stmt->execute([':id' => $libraryId]);
        
        $deletedCount = 0;
        foreach ($documents as $document) {
            if (isset($document['FILE_PATH']) && file_exists($document['FILE_PATH'])) {
                if (unlink($document['FILE_PATH'])) {
                    $deletedCount++;
                }
            }
        }
        
        return [
            'success' => true, 
            'message' => "Deleted {$deletedCount} files from library",
            'total' => count($documents)
        ];
    } catch (PDOException $e) {
        error_log("Database error deleting documents in library: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error deleting documents'];
    }
}

function getSubLibraries($parentLibraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT l.* 
            FROM Libraries l
            JOIN ChildLibraries c ON l.library_id = c.child_library_id
            WHERE c.library_id = :parent_id
            ORDER BY l.name
        ");
        $stmt->execute([':parent_id' => $parentLibraryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba az almappák lekérésekor: " . $e->getMessage());
        return [];
    }
}

function addLibraryWithParent($userId, $name, $parentLibraryId = null) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        $newLibraryId = null;
        $stmt = $conn->prepare("INSERT INTO Libraries (library_id, user_id, name) VALUES (LIBRARY_SEQ.NEXTVAL, :user_id, :name)");
        $stmt->execute([':user_id' => $userId, ':name' => $name]);
        
        $stmt = $conn->query("SELECT LIBRARY_SEQ.CURRVAL FROM DUAL");
        $newLibraryId = $stmt->fetchColumn();
        
        if (!$newLibraryId) {
            throw new Exception("Failed to get new library ID");
        }
        
        if ($parentLibraryId) {
            $stmt = $conn->prepare("SELECT library_id FROM Libraries WHERE library_id = :id");
            $stmt->execute([':id' => $parentLibraryId]);
            if (!$stmt->fetch()) {
                throw new Exception("Parent library does not exist");
            }
            
            $stmt = $conn->prepare("INSERT INTO ParentLibraries (parent_id, library_id, parent_library_id) VALUES (PARENTLIB_SEQ.NEXTVAL, :library_id, :parent_id)");
            $stmt->execute([
                ':library_id' => $newLibraryId,
                ':parent_id' => $parentLibraryId
            ]);
            
            $stmt = $conn->prepare("INSERT INTO ChildLibraries (child_id, library_id, child_library_id) VALUES (CHILDLIB_SEQ.NEXTVAL, :parent_id, :library_id)");
            $stmt->execute([
                ':parent_id' => $parentLibraryId,
                ':library_id' => $newLibraryId
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO ParentLibraries (parent_id, library_id, parent_library_id) VALUES (PARENTLIB_SEQ.NEXTVAL, :library_id, NULL)");
            $stmt->execute([':library_id' => $newLibraryId]);
        }
        
        $conn->commit();
        return $newLibraryId;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("PDO Error in addLibraryWithParent: " . $e->getMessage());
        throw $e;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error in addLibraryWithParent: " . $e->getMessage());
        throw $e;
    }
}

function getParentLibraryId($libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT parent_library_id FROM ParentLibraries WHERE library_id = :id");
        $stmt->execute([':id' => $libraryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['PARENT_LIBRARY_ID'] : null;
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a szülő mappa lekérésekor: " . $e->getMessage());
        return null;
    }
}

function getRootLibraries($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT l.* 
            FROM Libraries l
            JOIN ParentLibraries p ON l.library_id = p.library_id
            WHERE l.user_id = :user_id 
            AND p.parent_library_id IS NULL
            ORDER BY l.name
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a gyökér mappák lekérésekor: " . $e->getMessage());
        return [];
    }
}

function shareLibrary($libraryId, $sharedWithUserId, $permission = 'read') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM LibraryShares 
                                WHERE library_id = :library_id AND user_id = :user_id");
        $stmt->execute([
            ':library_id' => $libraryId,
            ':user_id' => $sharedWithUserId
        ]);
        
        if ($stmt->fetchColumn() > 0) {
            $stmt = $conn->prepare("UPDATE LibraryShares 
                                   SET permission = :permission 
                                   WHERE library_id = :library_id AND user_id = :user_id");
            $stmt->execute([
                ':permission' => $permission,
                ':library_id' => $libraryId,
                ':user_id' => $sharedWithUserId
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO LibraryShares (share_id, library_id, user_id, permission) 
                                   VALUES (LIBSHARE_SEQ.NEXTVAL, :library_id, :user_id, :permission)");
            $stmt->execute([
                ':library_id' => $libraryId,
                ':user_id' => $sharedWithUserId,
                ':permission' => $permission
            ]);
        }
        
        $subLibraries = getSubLibraries($libraryId);
        foreach ($subLibraries as $subLibrary) {
            shareLibrary($subLibrary['LIBRARY_ID'], $sharedWithUserId, $permission);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Sharing library error: " . $e->getMessage());
        return false;
    }
}

function getSharedLibraries($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT l.*, s.permission, u.username as shared_by
            FROM Libraries l
            JOIN LibraryShares s ON l.library_id = s.library_id
            JOIN Users u ON l.user_id = u.user_id
            WHERE s.user_id = :user_id
            ORDER BY l.name
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting shared libraries: " . $e->getMessage());
        return [];
    }
}

function getAllUsersExcept($currentUserId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT user_id, username, email FROM Users WHERE user_id != :current_user_id ORDER BY username");
        $stmt->execute([':current_user_id' => $currentUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

function getLibraryShares($libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT s.*, u.username, u.email 
            FROM LibraryShares s
            JOIN Users u ON s.user_id = u.user_id
            WHERE s.library_id = :library_id
        ");
        $stmt->execute([':library_id' => $libraryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting library shares: " . $e->getMessage());
        return [];
    }
}

function removeLibraryShare($libraryId, $userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM LibraryShares WHERE library_id = :library_id AND user_id = :user_id");
        $stmt->execute([
            ':library_id' => $libraryId,
            ':user_id' => $userId
        ]);
        
        $subLibraries = getSubLibraries($libraryId);
        foreach ($subLibraries as $subLibrary) {
            removeLibraryShare($subLibrary['LIBRARY_ID'], $userId);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error removing library share: " . $e->getMessage());
        return false;
    }
}

function getUserLibraryPermission($userId, $libraryId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT user_id FROM Libraries WHERE library_id = :library_id");
        $stmt->execute([':library_id' => $libraryId]);
        $libraryOwner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($libraryOwner && $libraryOwner['USER_ID'] == $userId) {
            return 'owner';
        }
        
        $stmt = $conn->prepare("SELECT permission FROM LibraryShares WHERE library_id = :library_id AND user_id = :user_id");
        $stmt->execute([
            ':library_id' => $libraryId,
            ':user_id' => $userId
        ]);
        $share = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($share) {
            return $share['PERMISSION'];
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error checking library permissions: " . $e->getMessage());
        return null;
    }
}

function getEditableLibraries($userId) {
    global $conn;
    
    try {
        $ownLibraries = getLibraries($userId);
        
        $stmt = $conn->prepare("
            SELECT l.*, u.username as owner_name, 'shared' as type
            FROM Libraries l
            JOIN LibraryShares s ON l.library_id = s.library_id
            JOIN Users u ON l.user_id = u.user_id
            WHERE s.user_id = :user_id AND s.permission = 'edit'
            ORDER BY l.name
        ");
        $stmt->execute([':user_id' => $userId]);
        $sharedLibraries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ownLibraries as &$lib) {
            $lib['type'] = 'own';
        }

        foreach ($sharedLibraries as &$lib) {
            $lib['type'] = $lib['TYPE'];
            unset($lib['TYPE']);
        }

        return array_merge($ownLibraries, $sharedLibraries);
    } catch (PDOException $e) {
        error_log("Error getting editable libraries: " . $e->getMessage());
        return [];
    }
}

function deleteLibraryRecursive($libraryId) {
    global $conn;
    
    deleteDocumentsInLibrary($libraryId);
    
    $subLibraries = getSubLibraries($libraryId);
    foreach ($subLibraries as $subLibrary) {
        deleteLibraryRecursive($subLibrary['LIBRARY_ID']);
    }
    
    $conn->prepare("DELETE FROM ChildLibraries WHERE library_id = :id OR child_library_id = :id")
         ->execute([':id' => $libraryId]);
    
    $conn->prepare("DELETE FROM ParentLibraries WHERE library_id = :id")
         ->execute([':id' => $libraryId]);
    
    $conn->prepare("DELETE FROM LibraryShares WHERE library_id = :id")
         ->execute([':id' => $libraryId]);
    
    deleteLibrary($libraryId);
}


function addCalendar($user_id, $name, $color) {
    global $conn;
        $stmt = $conn->prepare("INSERT INTO Calendars (calendar_id, user_id, name, color) 
                               VALUES (CALENDAR_SEQ.NEXTVAL, :user_id, :name, :color)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $name,
            ':color' => $color,
        ]);
}
function add_eventt($user_id, $title, $description, $start_time, $end_time, $location) {
    global $conn;
    $stmt1 = $conn->prepare("SELECT calendar_id FROM Calendars WHERE user_id = :user_id");
    $stmt1->execute([':user_id' => $user_id]);
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    if (!$result1) {
        die("Hiba: A megadott user_id-hez nem található calendar.");
    }
    $calendar_id = $result1['CALENDAR_ID'];

    $start_time = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $start_time)));
    $end_time = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $end_time)));
    $sql = "BEGIN addevent(:user_id, :calendar_id, :title, :description, 
        TO_TIMESTAMP(:start_time, 'YYYY-MM-DD HH24:MI:SS'), 
        TO_TIMESTAMP(:end_time, 'YYYY-MM-DD HH24:MI:SS'),  :location); END;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':calendar_id' => $calendar_id,
        ':title' => $title,
        ':description' => $description,
        ':start_time' => $start_time,
        ':end_time' => $end_time,
        ':location' => $location
    ]);
}



function deleteCalendar($calendar_id) {
    global $conn;
    try {        
        $stmt = $conn->prepare("DELETE FROM Calendars WHERE calendar_id = :calendar_id");
        $stmt->execute([':calendar_id' => $calendar_id]);
        return ['success' => true, 'message' => 'Calendar deleted successfully'];
    } catch (PDOException $e) {
        error_log("Database error deleting calendar: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error while deleting calendar'];
    } 
}

function updateCalendar($calendar_id, $name, $color) {
    global $conn;
    
    try {
        $sql = "UPDATE Calendars SET name = :name, color = :color";
        $params = [
            ':name' => $name,
            ':calendar_id' => $calendar_id
        ];
        
        $sql .= " WHERE calendar_id = :calendar_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return true;
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a naptár frissítésekor: " . $e->getMessage());
        return false;
    }
}
function getEvent($eventID) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM Events WHERE event_id = :id");
        $stmt->execute([':id' => $eventID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Adatbázis hiba a dokumentum lekérésekor: " . $e->getMessage());
        return false;
    }
}




function event_leaderboard() {
    global $conn;

    $sql = "
        SELECT u.username, COUNT(e.event_id) AS event_count
        FROM Users u
        LEFT JOIN Events e ON u.user_id = e.user_id
        GROUP BY u.username
        ORDER BY event_count DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function document_leaderboard() {
    global $conn;

    $sql = "
        SELECT u.username, COUNT(d.document_id) AS document_count
        FROM Users u
        LEFT JOIN Documents d ON u.user_id = d.user_id
        GROUP BY u.username
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $doc_counts = [];
    foreach ($results as $row) {
        $doc_counts[$row['USERNAME']] = $row['DOCUMENT_COUNT'];
    }

    return $doc_counts;
}

function library_leaderboard() {
    global $conn;

    $sql= "
        SELECT u.username, COUNT(l.library_id) AS library_count
        FROM Users u
        LEFT JOIN Libraries l ON u.user_id = l.user_id
        GROUP BY u.username
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lib_counts = [];

    foreach ($results as $row){
        $lib_counts[$row['USERNAME']] = $row['LIBRARY_COUNT'];
    }

    return $lib_counts;

}

function average_leaderboard() {
    global $conn;

    $sql = "
        SELECT
            ROUND(AVG(event_count)) AS avg_event_count,
            ROUND(AVG(document_count)) AS avg_document_count,
            ROUND(AVG(library_count)) AS avg_library_count
        FROM (
            SELECT
                u.username,
                COUNT(DISTINCT e.event_id) AS event_count,
                COUNT(DISTINCT d.document_id) AS document_count,
                COUNT(DISTINCT l.library_id) AS library_count
            FROM Users u
            LEFT JOIN Events e ON u.user_id = e.user_id
            LEFT JOIN Documents d ON u.user_id = d.user_id
            LEFT JOIN Libraries l ON u.user_id = l.user_id
            GROUP BY u.username
        )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


?>