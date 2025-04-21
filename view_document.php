<?php
session_start();
include 'functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$documentId = (int)$_GET['id'];

$document = getDocumentById($documentId);

if (!$document) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($document['NAME']); ?> - Drive Klón</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <?php include 'components/top_navbar.php'; ?>
  <?php include 'components/sidebar_navbar.php'; ?>

  <div class="main-content">
    <div class="breadcrumbs">
      <a href="index.php">Saját fájlok</a>
      <?php if (isset($document['LIBRARY_ID']) && $document['LIBRARY_ID']): ?>
        <?php 
        $libraryName = '';
        try {
            $library = getLibraryById($document['LIBRARY_ID']);
            if ($library) {
                $libraryName = $library['NAME'];
            }
        } catch (Exception $e) {
            error_log("Hiba a mappa lekérdezésekor: " . $e->getMessage());
        }
        ?>
        &gt; <a href="library.php?id=<?php echo $document['LIBRARY_ID']; ?>"><?php echo htmlspecialchars($libraryName); ?></a>
      <?php endif; ?>
      &gt; <span><?php echo htmlspecialchars($document['NAME']); ?></span>
    </div>
    
    <div class="document-header">
      <h1><?php echo htmlspecialchars($document['NAME']); ?></h1>
      <div class="document-actions">
        <a href="edit_document.php?id=<?php echo $documentId; ?>" class="btn-secondary">Szerkesztés</a>
      </div>
    </div>
    
    <div class="document-meta">
      <p>Létrehozva: 
        <?php 
        if (isset($document['CREATED_AT']) && !empty($document['CREATED_AT'])) {
            if (preg_match('/(\d{2})-([A-Z]{3})-(\d{2})/', $document['CREATED_AT'], $matches)) {
                $day = $matches[1];
                $monthAbbr = $matches[2];
                $year = $matches[3];
                
                $monthMap = [
                    'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 
                    'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 
                    'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12
                ];
                
                $monthNum = isset($monthMap[$monthAbbr]) ? $monthMap[$monthAbbr] : 1;
                
                $fullYear = (int)$year >= 70 ? '19' . $year : '20' . $year;
                
                $hungarianMonths = [
                    1 => 'JAN.', 2 => 'FEBR.', 3 => 'MÁRC.', 4 => 'ÁPR.',
                    5 => 'MÁJ.', 6 => 'JÚN.', 7 => 'JÚL.', 8 => 'AUG.',
                    9 => 'SZEPT.', 10 => 'OKT.', 11 => 'NOV.', 12 => 'DEC.'
                ];
                
                $hungarianMonth = $hungarianMonths[$monthNum];
                
                echo "$fullYear. $hungarianMonth $day";
            } else {
                echo htmlspecialchars($document['CREATED_AT']);
            }
        } else {
            echo '<span class="no-data">Nincs adat</span>';
        }
        ?>
      </p>
      <p>Módosítva: 
        <?php 
        if (isset($document['UPDATED_AT']) && !empty($document['UPDATED_AT'])) {
            if (preg_match('/(\d{2})-([A-Z]{3})-(\d{2})/', $document['UPDATED_AT'], $matches)) {
                $day = $matches[1];
                $monthAbbr = $matches[2];
                $year = $matches[3];
                
                $monthMap = [
                    'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 
                    'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 
                    'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12
                ];
                
                $monthNum = isset($monthMap[$monthAbbr]) ? $monthMap[$monthAbbr] : 1;
                
                $fullYear = (int)$year >= 70 ? '19' . $year : '20' . $year;
                
                $hungarianMonths = [
                    1 => 'JAN.', 2 => 'FEBR.', 3 => 'MÁRC.', 4 => 'ÁPR.',
                    5 => 'MÁJ.', 6 => 'JÚN.', 7 => 'JÚL.', 8 => 'AUG.',
                    9 => 'SZEPT.', 10 => 'OKT.', 11 => 'NOV.', 12 => 'DEC.'
                ];
                
                $hungarianMonth = $hungarianMonths[$monthNum];
                
                echo "$fullYear. $hungarianMonth $day";
            } else {
                echo htmlspecialchars($document['UPDATED_AT']);
            }
        } else {
            echo '<span class="no-data">Nincs adat</span>';
        }
        ?>
      </p>
    </div>
    
    <div class="document-content">
      <?php 
      if (isset($document['CONTENT']) && $document['CONTENT']) {
          echo nl2br(htmlspecialchars($document['CONTENT']));
      } elseif (isset($document['FILE_PATH']) && $document['FILE_PATH']) {
          $filePath = $document['FILE_PATH'];
          $fileType = isset($document['FILE_TYPE']) ? $document['FILE_TYPE'] : '';
          
          if (strpos($fileType, 'image/') === 0) {
              echo '<img src="' . htmlspecialchars($filePath) . '" alt="' . htmlspecialchars($document['NAME']) . '" class="document-image">';
          } elseif (strpos($fileType, 'video/') === 0) {
              echo '<video controls class="document-video"><source src="' . htmlspecialchars($filePath) . '" type="' . htmlspecialchars($fileType) . '">A böngésző nem támogatja a videólejátszást.</video>';
          } elseif (strpos($fileType, 'audio/') === 0) {
              echo '<audio controls class="document-audio"><source src="' . htmlspecialchars($filePath) . '" type="' . htmlspecialchars($fileType) . '">A böngésző nem támogatja a hanglejátszást.</audio>';
          } else {
              echo '<p>A fájl nem megjeleníthető közvetlenül a böngészőben.</p>';
              echo '<a href="' . htmlspecialchars($filePath) . '" class="btn-primary" download>Letöltés</a>';
          }
      } else {
          echo '<p>Nincs elérhető tartalom.</p>';
      }
      ?>
    </div>
  </div>

</body>
</html>