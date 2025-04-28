<?php
// 1) start session & db
require_once __DIR__ . '/php/config.php';  // config.php should do session_start() + provide $pdo
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
// 2) nav
require_once __DIR__ . '/partials/nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Favorites – Swedish Recipes</title>
  <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<section class="favorites-container">
  <div class="container">
    <h1>My Favorite Recipes</h1>
    <div class="recipe-grid" id="favorites-container"><!-- filled by JS --></div>
    <div id="no-favorites" style="display:none;">
      <!-- “no favorites yet” message -->
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
<div id="toast" class="toast"></div>

<!-- 3) load your scripts -->
<script src="js/auth.js"></script>
<script src="js/favorites.js"></script>
</body>
</html>