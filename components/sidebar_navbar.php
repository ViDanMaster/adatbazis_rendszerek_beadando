<nav class="navbar">
  <h2>Tárhely</h2>
  <ul>
    <li><a href="add_library.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add_library.php' ? 'class="active"' : ''; ?>>Új mappa</a></li>
    <li><a href="add_document.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add_document.php' ? 'class="active"' : ''; ?>>Új fájl</a></li>
    <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Saját fájlok</a></li>
    <li><a href="shared_with_me.php" <?php echo basename($_SERVER['PHP_SELF']) == 'shared_with_me.php' ? 'class="active"' : ''; ?>>Megosztva velem</a></li>
    <li><a href="calendar.php" <?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'class="active"' : ''; ?>>Naptáram</a></li>
    <li><a href="add_event.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add_event.php' ? 'class="active"' : ''; ?>>Új esemény</a></li>
  </ul>
</nav>