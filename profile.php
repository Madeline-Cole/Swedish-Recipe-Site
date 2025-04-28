<?php
  require_once __DIR__ . '/php/config.php';
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Swedish Recipes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<section class="profile-container">
  <div class="container">
    <div class="profile-header">
      <h1>My Profile</h1>
    </div>
    <div class="profile-content">

       <!-- SIDEBAR -->
       <div class="profile-sidebar">
        <div class="profile-avatar">
          <img id="profile-image" src="images/default-avatar.png" alt="Avatar">
          <button id="change-avatar-btn" class="btn btn-small">Change Photo</button>
          <input type="file" id="avatar-upload" accept="image/*" style="display:none">
        </div>
        <div class="nav-wrapper">
          <ul class="profile-nav">
            <li class="active"><a href="#account-info">Account Information</a></li>
            <li><a href="#saved-recipes">Saved Recipes</a></li>
            <li><a href="#preferences">Preferences</a></li>
          </ul>
          <div id="tab-indicator"></div>
        </div>
      </div>

      <!-- MAIN -->
      <div class="profile-main">
        <section id="account-info" class="profile-section active">
          <?php include __DIR__ . '/partials/profile-sections/account.php'; ?>
        </section>
        <section id="saved-recipes" class="profile-section">
          <?php include __DIR__ . '/partials/profile-sections/saved.php'; ?>
        </section>
        <section id="preferences" class="profile-section">
          <?php include __DIR__ . '/partials/profile-sections/preferences.php'; ?>
        </section>
      </div>

    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

<div id="toast" class="toast"></div>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>
<script src="js/profile.js"></script>
<script src="js/favorites.js"></script>

</body>
</html>