<?php
require_once __DIR__ . '/php/config.php';
?>
<?php

// Get the category from URL (like ?category=breakfast)
$category = $_GET['category'] ?? '';

if (!$category) {
    // Redirect to home if no category selected
    header('Location: index.php');
    exit;
}

function slugify($string) {
  return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

$stmt = $pdo->query("SELECT id, title FROM recipes WHERE slug IS NULL OR slug = ''");
while ($row = $stmt->fetch()) {
  $slug = slugify($row['title']);
  $update = $pdo->prepare("UPDATE recipes SET slug = ? WHERE id = ?");
  $update->execute([$slug, $row['id']]);
  echo "Updated recipe ID {$row['id']} with slug: $slug<br>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ucfirst(htmlspecialchars($category)); ?> Recipes - Swedish Recipes</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<?php
require_once __DIR__ . '/partials/nav.php';
$categoryLabels = [
  'breakfast' => ['label' => 'Breakfast', 'desc' => 'Start your day with a taste of Sweden'],
  'lunch' => ['label' => 'Lunch', 'desc' => 'Hearty mid-day meals inspired by tradition'],
  'dinner' => ['label' => 'Dinner', 'desc' => 'Delicious dinner recipes for the whole family'],
  'dessert' => ['label' => 'Dessert', 'desc' => 'Sweet treats to satisfy every craving'],
  'drinks' => ['label' => 'Drinks', 'desc' => 'Refreshments for any occasion, hot or cold'],
  'snacks' => ['label' => 'Snacks', 'desc' => 'Small bites with big Swedish flavor'],
  'appetizers' => ['label' => 'Appetizers', 'desc' => 'Light starters to whet your appetite'],
  'user-uploaded' => ['label' => 'User Recipes', 'desc' => 'Recipes shared by our lovely users!']
];

$label = $categoryLabels[$category]['label'] ?? ucfirst($category);
$desc = $categoryLabels[$category]['desc'] ?? 'Explore delicious recipes from our collection.';
?>

<section class="category-header">
  <div class="container">
    <h1><?php echo htmlspecialchars($label); ?> Recipes</h1>
    <p><?php echo htmlspecialchars($desc); ?></p>
  </div>
</section>

<section class="featured-recipes">
  <div class="container">
  <h2 class="category-heading"><?php echo htmlspecialchars($label); ?> Recipes</h2>
    <div class="back-to-all">
    <a href="all-recipes.php" class="btn">← Back to All Recipes</a>
</div>
    <div class="recipe-grid">
<?php
// Fetch recipes where category matches (allow partial matches like 'dessert,breakfast')
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE category LIKE ?");
$stmt->execute(["%$category%"]);
$recipes = $stmt->fetchAll();

if ($recipes):
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
<?php
  endforeach;
else:
?>
      <p>No recipes found in this category!</p>
<?php endif; ?>
    </div>

  </div>
</section>

<!-- Footer -->
<?php include 'partials/footer.php'; ?>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>
<script src="js/favorites.js"></script>

</body>
</html>