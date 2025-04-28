<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/partials/nav.php';
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swedish Recipes - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body> 
    <section class="hero">
        <div class="container">
            <h2>Delicious Recipes For Every Occasion</h2>
            <p>Discover easy-to-make recipes with adjustable serving sizes</p>
            <div class="search-bar">
            <input type="text" placeholder="Search recipes..." aria-label="Search recipes">
                <button><i class="fas fa-search"></i></button>
                <button id="clear-filters" class="btn-small">Clear Filters</button>
                <div class="filter-toggles" id="filter-toggles"></div>
        </div>
            </div>
        </div>
    </section>

    <section class="featured-recipes">
        <div class="container">
            <h2>Featured Recipes</h2>
            <div class="recipe-grid">
<?php
// Fetch 6 featured recipes, you can change LIMIT as needed
$stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC LIMIT 6");
$recipes = $stmt->fetchAll();

// No manual decode here
// Pull user preferences
$userPreferences = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT preferences FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['preferences'])) {
        $userPreferences = json_decode($user['preferences'], true);
    }
}

foreach ($recipes as $recipe):
    $isFavorited = false;
if (isset($_SESSION['user_id'])) {
    $checkFav = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $checkFav->execute([$_SESSION['user_id'], $recipe['id']]);
    $isFavorited = $checkFav->fetchColumn() !== false;
}

    $isNew = strtotime($recipe['created_at']) > strtotime('-7 days');
    $ingredientText = strtolower($recipe['ingredients']);
    $conflictTags = [];

    if (!empty($userPreferences)) {
        $conflictRules = [
            'vegetarian' => ['beef', 'pork', 'chicken', 'meat', 'bacon', 'sausage', 'turkey', 'ham', 'lamb', 'duck'],
            'vegan' => ['beef', 'pork', 'chicken', 'meat', 'bacon', 'sausage', 'turkey', 'ham', 'lamb', 'duck', 'milk', 'butter', 'cheese', 'cream', 'egg', 'honey', 'yogurt'],
            'gluten-free' => ['flour', 'bread', 'pasta', 'barley', 'rye', 'wheat', 'cracker', 'cookie', 'cereal'],
            'dairy-free' => ['milk', 'butter', 'cheese', 'cream', 'yogurt', 'ice cream'],
            'nut-free' => ['almond', 'walnut', 'cashew', 'pecan', 'hazelnut', 'macadamia', 'brazil nut', 'pistachio'],
            'peanut-free' => ['peanut', 'peanut butter'],
            'almond-free' => ['almond'],
            'egg-free' => ['egg', 'mayonnaise', 'meringue'],
            'shellfish-free' => ['shrimp', 'crab', 'lobster', 'scallop', 'clam', 'oyster', 'mussel'],
            'fish-free' => ['salmon', 'tuna', 'cod', 'trout', 'haddock', 'tilapia'],
            'soy-free' => ['soy', 'soybean', 'tofu', 'soy sauce', 'edamame'],
            'sesame-free' => ['sesame', 'tahini'],
            'corn-free' => ['corn', 'cornmeal', 'corn syrup', 'popcorn'],
            'low-sugar' => ['sugar', 'syrup', 'candy', 'chocolate', 'dessert', 'cookie'],
            'low-sodium' => ['salt', 'soy sauce', 'processed', 'cured', 'bacon', 'ham'],
            'kosher' => [] // Kosher detection is complex
        ];

        foreach ($userPreferences as $preference) {
            if (isset($conflictRules[$preference])) {
                foreach ($conflictRules[$preference] as $badWord) {
                    if (strpos($ingredientText, $badWord) !== false) {
                        switch ($preference) {
                            case 'vegetarian': $conflictTags[] = 'Non-Vegetarian'; break;
                            case 'vegan': $conflictTags[] = 'Non-Vegan'; break;
                            case 'gluten-free': $conflictTags[] = 'Contains Gluten'; break;
                            case 'dairy-free': $conflictTags[] = 'Contains Dairy'; break;
                            case 'nut-free': $conflictTags[] = 'Contains Tree Nuts'; break;
                            case 'peanut-free': $conflictTags[] = 'Contains Peanuts'; break;
                            case 'almond-free': $conflictTags[] = 'Contains Almonds'; break;
                            case 'egg-free': $conflictTags[] = 'Contains Eggs'; break;
                            case 'shellfish-free': $conflictTags[] = 'Contains Shellfish'; break;
                            case 'fish-free': $conflictTags[] = 'Contains Fish'; break;
                            case 'soy-free': $conflictTags[] = 'Contains Soy'; break;
                            case 'sesame-free': $conflictTags[] = 'Contains Sesame'; break;
                            case 'corn-free': $conflictTags[] = 'Contains Corn'; break;
                            case 'low-sugar': $conflictTags[] = 'High Sugar Content'; break;
                            case 'low-sodium': $conflictTags[] = 'High Sodium Content'; break;
                            case 'kosher': $conflictTags[] = 'Non-Kosher (Verify)'; break;
                        }
                        break; // Stop scanning this preference after first match
                    }
                }
            }
        }
    }

    $conflictTags = array_unique($conflictTags);
?>

<!-- Recipe Card Output -->
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
        <h3>
            <?php echo htmlspecialchars($recipe['title']); ?>
            <?php if (!empty($conflictTags)): ?>
                <span class="conflict-warning" title="May contain allergens or conflicts">⚠️</span>
            <?php endif; ?>
        </h3>

        <?php foreach ($conflictTags as $tag): ?>
            <div class="conflict-tag"><?php echo htmlspecialchars($tag); ?></div>
        <?php endforeach; ?>


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
    <i class="<?php echo $isFavorited ? 'fas' : 'far'; ?> fa-heart"></i>
</button>
    </div>
</div>

<?php endforeach; ?>

</div>          
        </div>
    </section>

    <div class="view-all-recipes">
    <a href="all-recipes.php" class="btn">View All Recipes</a>
</div>

<div class="view-all-recipes">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="favorites.php" class="btn"><i class="fas fa-heart"></i> See Saved Recipes</a>
  <?php else: ?>
    <a href="login.php?redirect=favorites.php" class="btn"><i class="fas fa-heart"></i> See Saved Recipes</a>
  <?php endif; ?>
</div>

    <section class="categories">
        <div class="container">
            <h2>Browse By Category</h2>
            <div class="category-grid">
            <a href="category.php?category=breakfast" class="category-item">
                    <i class="fas fa-coffee"></i>
                    <h3>Breakfast</h3>
                </a>
                <a href="category.php?category=lunch" class="category-item">
                    <i class="fas fa-hamburger"></i>
                    <h3>Lunch</h3>
                </a>
                <a href="category.php?category=dinner" class="category-item">
                    <i class="fas fa-utensils"></i>
                    <h3>Dinner</h3>
                </a>
                <a href="category.php?category=dessert" class="category-item">
                    <i class="fas fa-cookie"></i>
                    <h3>Desserts</h3>
                </a>
            </div>
        </div>
    </section>

       
    <?php include 'partials/footer.php'; ?>


    <div id="toast" class="toast"></div>
    <div id="tags-container" class="tags"></div>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>

<script>
  const userPreferences = <?php echo json_encode($userPreferences); ?>;
  const recipeCards     = document.querySelectorAll('.recipe-card');
</script>

</body>
</html>