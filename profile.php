<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';
$error = '';

$userData = getUserData($userId);

if (isset($_POST['update_email'])) {
    $newEmail = trim($_POST['email']);
    
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $conn->prepare("UPDATE Users SET email = :email WHERE user_id = :user_id");
            $stmt->execute([':email' => $newEmail, ':user_id' => $userId]);
            $message = "Email sikeresen frissítve!";
            $userData = getUserData($userId);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique constraint') !== false) {
                $error = "Ez az email cím már használatban van!";
            } else {
                $error = "Hiba történt az email frissítésekor: " . $e->getMessage();
            }
        }
    } else {
        $error = "Érvénytelen email formátum!";
    }
}

if (isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Minden jelszó mező kitöltése kötelező!";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Az új jelszavak nem egyeznek!";
    } elseif (strlen($newPassword) < 6) {
        $error = "Az új jelszónak legalább 6 karakter hosszúnak kell lennie!";
    } else {
        if (password_verify($currentPassword, $userData['PASSWORD'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            try {
                $stmt = $conn->prepare("UPDATE Users SET password = :password WHERE user_id = :user_id");
                $stmt->execute([':password' => $hashedPassword, ':user_id' => $userId]);
                $message = "Jelszó sikeresen frissítve!";
            } catch (PDOException $e) {
                $error = "Hiba történt a jelszó frissítésekor: " . $e->getMessage();
            }
        } else {
            $error = "A jelenlegi jelszó helytelen!";
        }
    }
}

if (isset($_POST['update_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        
        if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
            $error = "Csak JPG, PNG és GIF formátumú képek engedélyezettek!";
        } elseif ($_FILES['profile_picture']['size'] > $maxSize) {
            $error = "A kép mérete nem haladhatja meg az 5MB-ot!";
        } else {
            $uploadDir = "uploads/user_{$userId}/profile_picture/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $newFilename = "profile_" . time() . "." . $fileExtension;
            $targetFilePath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                if (!empty($userData['PROFILE_PICTURE']) && file_exists($userData['PROFILE_PICTURE'])) {
                    unlink($userData['PROFILE_PICTURE']);
                }
                
                try {
                    $stmt = $conn->prepare("UPDATE Users SET profile_picture = :profile_picture WHERE user_id = :user_id");
                    $stmt->execute([':profile_picture' => $targetFilePath, ':user_id' => $userId]);
                    $message = "Profilkép sikeresen frissítve!";
                    $userData = getUserData($userId);
                } catch (PDOException $e) {
                    $error = "Hiba történt a profilkép frissítésekor: " . $e->getMessage();
                }
            } else {
                $error = "Hiba történt a fájl feltöltésekor!";
            }
        }
    } else if ($_FILES['profile_picture']['error'] != 4) {
        $error = "Hiba történt a fájl feltöltésekor! Hiba kód: " . $_FILES['profile_picture']['error'];
    } else {
        $error = "Nincs kiválasztva profilkép!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil kezelése - Goofle</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

    <?php include 'components/top_navbar.php'; ?>
    <?php include 'components/sidebar_navbar.php'; ?>

    <div class="main-content">
        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-picture">
                <h2>Profilkép</h2>
                <?php if (!empty($userData['PROFILE_PICTURE']) && file_exists($userData['PROFILE_PICTURE'])): ?>
                    <img src="<?php echo $userData['PROFILE_PICTURE']; ?>" alt="Profilkép">
                <?php else: ?>
                    <div class="no-image">Nincs profilkép</div>
                <?php endif; ?>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture">Új profilkép kiválasztása:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <button type="submit" name="update_picture" class="btn">Profilkép frissítése</button>
                </form>
            </div>
            
            <div class="profile-info">
                <h2>Felhasználói adatok</h2>
                
                <div class="user-detail">
                    <span class="label">Felhasználónév:</span>
                    <span class="value"><?php echo htmlspecialchars($username); ?></span>
                    <small>(A felhasználónév nem módosítható)</small>
                </div>
                
                <form action="" method="post">
                    <h3>Email cím módosítása</h3>
                    <div class="form-group">
                        <label for="email">Email cím:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['EMAIL']); ?>" required>
                    </div>
                    <button type="submit" name="update_email" class="btn">Email frissítése</button>
                </form>
                
                <form action="" method="post">
                    <h3>Jelszó módosítása</h3>
                    <div class="form-group">
                        <label for="current_password">Jelenlegi jelszó:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Új jelszó:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Új jelszó megerősítése:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn">Jelszó frissítése</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>