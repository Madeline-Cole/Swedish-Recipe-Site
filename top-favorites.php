<?php require_once __DIR__ . '/php/config.php'
include 'partials/nav.php'; ?>
<?php
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
  <title>Top 10 Most Saved Recipes</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/top-favorites.css">
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<section id="top-favorites-section">
  <div class="container">
    <h2>Top 10 Most Saved Recipes</h2>
    <div id="top-favorites-container" class="recipe-grid">
    <?php
    $stmt = $pdo->query("
        SELECT recipes.id, recipes.title, recipes.image, recipes.description, recipes.category, recipes.time, recipes.calories, recipes.servings, COUNT(favorites.id) AS total
        FROM favorites
        JOIN recipes ON recipes.id = favorites.recipe_id
        GROUP BY recipes.id
        ORDER BY total DESC
        LIMIT 10
    ");
    $topRecipes = $stmt->fetchAll();

    if ($topRecipes):
        foreach ($topRecipes as $recipe):
    ?>
        <div class="recipe-card">
            <div class="recipe-image">
                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            </div>
            <div class="recipe-content">
                <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                <p class="recipe-meta">
                    <span><i class="far fa-clock"></i> <?php echo (int)$recipe['time']; ?> mins</span>
                    <span><i class="fas fa-fire"></i> <?php echo (int)$recipe['calories']; ?> cal</span>
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
        echo "<p>No top favorites found yet. Be the first to favorite some recipes!</p>";
    endif;
    ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<div id="toast" class="toast"></div>
<div id="tags-container" class="tags"></div>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>
<script src="js/favorites.js"></script>

</body>
</html>
