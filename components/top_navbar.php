<header class="top-navbar">
  <div class="logo">
    <a href="index.php" class="logo-link"><h2>Goofle</h2></a>
  </div>
  <div class="top-nav-links">
    <a href="groups.php">Csoportok</a>
  </div>
  <div class="user-actions">
    <?php
    if (isset($_SESSION['user_id'])) {
      echo '<span class="username">Üdv, ' . htmlspecialchars($_SESSION['username']) . '!</span>';
      echo '<a href="profile.php" class="profile-link">Profil</a>';
      echo '<a href="logout.php" class="logout-btn">Kijelentkezés</a>';
    } else {
      echo '<a href="login.php" class="login-btn">Bejelentkezés</a>';
      echo '<a href="register.php" class="register-btn">Regisztráció</a>';
    }
    ?>
  </div>
</header>