<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/partials/nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Recipes - Swedish Recipes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>

<section class="featured-recipes">
    <div class="container">
        <h2>All Recipes</h2>
        <div class="recipe-grid">
<?php
// Fetch ALL recipes
$stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC");
$recipes = $stmt->fetchAll();

foreach ($recipes as $recipe):
    $isNew = strtotime($recipe['created_at']) > strtotime('-7 days');
    // determine favorite state:
  $isFavorited = false;
  if (isset($_SESSION['user_id'])) {
    $chk = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $chk->execute([ $_SESSION['user_id'], $recipe['id'] ]);
    $isFavorited = (bool)$chk->fetchColumn();
  }
?>

    <div class="recipe-card" data-category="<?php echo htmlspecialchars($recipe['category']); ?>">
        <?php if ($isNew): ?>
            <div class="new-badge">NEW!</div>
        <?php endif; ?>
        <div class="recipe-image">
      <img src="<?=htmlspecialchars($recipe['image'])?>" alt="<?=htmlspecialchars($recipe['title'])?>">
      
      <!-- ← add this button -->
      <button
        class="favorite-btn <?= $isFavorited ? 'active' : '' ?>"
        data-id="<?= htmlspecialchars($recipe['id']) ?>"
        aria-label="<?= $isFavorited ? 'Remove from favorites' : 'Add to favorites' ?>"
      >
        <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i>
      </button>
    </div>
        <div class="recipe-content">
            <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
            <p class="recipe-meta">
                <span><i class="far fa-clock"></i> <?php echo (int)$recipe['time']; ?> mins</span>
                <span><i class="fas fa-utensils"></i> <?php echo (int)$recipe['servings']; ?> servings</span>
            </p>
            <p class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 80)); ?>...</p>
            <a
  href="recipe.php?
    <?= !empty($recipe['slug'])
        ? 'slug=' . urlencode($recipe['slug'])
        : 'id='   . urlencode($recipe['id']) ?>"
  class="btn btn-small"
>
  View Recipe
</a>
        </div>
    </div>
<?php endforeach; ?>
</div>          
    </div>
</section>

<!-- Footer Copy same as index.php -->
<?php include 'partials/footer.php'; ?>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>
</body>
</html>