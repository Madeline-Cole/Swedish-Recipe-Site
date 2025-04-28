<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/partials/nav.php';
?>

<?php
if (!$pdo) {
    die("Database connection failed.");
}

try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    die("DB check failed: " . $e->getMessage());
}

// Fetch recipe from database
error_log("Loading recipe ID: $recipe_id");

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE slug = ?");
$stmt->execute([$slug]);

$recipe = $stmt->fetch();

if (!$recipe) {
    echo "Recipe not found.";
    exit;
}

// Function to clean text output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Load user's preferences if logged in
$userPreferences = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT preferences FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['preferences'])) {
        $userPreferences = json_decode($user['preferences'], true);
    }
}

// Scan ingredients to detect allergens / conflicts
$ingredientText = strtolower($recipe['ingredients']);

$allergyConflicts = [];

// Basic checks
if (in_array('vegetarian', $userPreferences) && (strpos($ingredientText, 'beef') !== false || strpos($ingredientText, 'chicken') !== false || strpos($ingredientText, 'pork') !== false || strpos($ingredientText, 'meat') !== false)) {
    $allergyConflicts[] = 'This recipe contains meat, but you are vegetarian.';
}
if (in_array('gluten-free', $userPreferences) && strpos($ingredientText, 'flour') !== false) {
    $allergyConflicts[] = 'This recipe contains gluten (flour).';
}
if (in_array('dairy-free', $userPreferences) && (strpos($ingredientText, 'butter') !== false || strpos($ingredientText, 'milk') !== false || strpos($ingredientText, 'cream') !== false)) {
    $allergyConflicts[] = 'This recipe contains dairy.';
}
if (in_array('peanut-free', $userPreferences) && strpos($ingredientText, 'peanut') !== false) {
    $allergyConflicts[] = 'This recipe contains peanuts.';
}
if (in_array('almond-free', $userPreferences) && strpos($ingredientText, 'almond') !== false) {
    $allergyConflicts[] = 'This recipe contains almonds.';
}
if (in_array('egg-free', $userPreferences) && strpos($ingredientText, 'egg') !== false) {
    $allergyConflicts[] = 'This recipe contains eggs.';
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

$isFavorited = false;
if (isset($_SESSION['user_id'])) {
  $checkFav = $pdo->prepare(
    "SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?"
  );
  $checkFav->execute([ $_SESSION['user_id'], $recipe['id'] ]);
  $isFavorited = (bool) $checkFav->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($recipe['title']); ?> - Swedish Recipes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<?php
$isAdapted = !empty($recipe['source_url']);
?>

<?php if (!empty($allergyConflicts)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('toast');
    const message = `⚠️ CONTAINS DIETARY RESTRICTION:\n<?php echo implode('\n', array_map('addslashes', $allergyConflicts)); ?>`;
    if (toast) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 5000);
    }
});
</script>
<?php endif; ?>

<section class="recipe-detail">
    <div class="container">
        <div class="recipe-header">
        <h1>
    <?php echo e($recipe['title']); ?>
    <?php if (strpos(strtolower($recipe['tags']), 'farmor') !== false): ?>
        <span class="badge-farmor">👵 From Farmor</span>
    <?php endif; ?>
</h1>
            <p class="recipe-meta">
                <?php if (!empty($recipe['prep_time'])): ?>
                    <span><i class="far fa-clock"></i> Prep: <?php echo e($recipe['prep_time']); ?> mins</span>
                <?php endif; ?>
                <?php if (!empty($recipe['cook_time'])): ?>
                    <span><i class="fas fa-fire"></i> Cook: <?php echo e($recipe['cook_time']); ?> mins</span>
                <?php endif; ?>
                <?php if (!empty($recipe['temperature'])): ?>
                    <span><i class="fas fa-temperature-high"></i> <?php echo e($recipe['temperature']); ?></span>
                <?php endif; ?>
                <?php if (!empty($recipe['servings'])): ?>
                    <span><i class="fas fa-utensils"></i> Serves: <span id="serving-size"><?php echo e($recipe['servings']); ?></span></span>
                <?php endif; ?>
                </p> <!-- CLOSE your recipe-meta paragraph properly first -->


<p class="recipe-origin-type">
    <?php if ($isAdapted): ?>
        <i class="fas fa-link"></i> Adapted from another source
    <?php else: ?>
        <i class="fas fa-heart"></i> Original family recipe
    <?php endif; ?>
</p>

<?php if (!empty($recipe['source_url']) || !empty($recipe['source_note'])): ?>
    <div class="recipe-source">
        <?php if (!empty($recipe['source_url'])): ?>
            <p>This is not my original recipe. Check out the <a href="<?php echo e($recipe['source_url']); ?>" target="_blank" rel="noopener noreferrer">original recipe here</a>.</p>
        <?php endif; ?>
        <?php if (!empty($recipe['source_note'])): ?>
            <p><em><?php echo e($recipe['source_note']); ?></em></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
        </div>

        <div class="recipe-image-large">
            <img src="<?php echo e($recipe['image']); ?>" alt="<?php echo e($recipe['title']); ?>">
        </div>

        <div class="recipe-actions">
            <div class="serving-adjuster">
                <label for="servings">Adjust servings:</label>
                <div class="serving-controls">
                    <button id="decrease-servings" class="btn-small"><i class="fas fa-minus"></i></button>
                    <span id="servings"><?php echo e($recipe['servings']); ?></span>
                    <button id="increase-servings" class="btn-small"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="recipe-buttons">
                <button class="btn"><i class="fas fa-print"></i> Print</button>
                <button class="favorite-btn <?php echo $isFavorited?'active':''?>"
          data-id="<?php echo $recipe['id']?>">
    <i class="<?php echo $isFavorited?'fas':'far'?> fa-heart"></i>
  </button>
            </div>
            <div style="margin-top: 10px;">
  <a href="favorites.php" class="btn btn-small"><i class="fas fa-heart"></i> See Saved Recipes</a>
</div>
        </div>

        <div class="recipe-content-wrapper">
            <div class="ingredients-section">
                <h2>Ingredients</h2>
                <ul id="ingredients-list">
                <?php foreach (explode("\n", $recipe['ingredients']) as $ingredient): ?>
    <?php
    preg_match('/^(\d+[\d\/\.]*)\s*(.*)/', trim($ingredient), $matches);
    $quantity = $matches[1] ?? '1';
    $text = $matches[2] ?? trim($ingredient);
    ?>
    <li><span class="quantity" data-base="<?php echo floatval(eval("return $quantity;")); ?>"><?php echo $quantity; ?></span> <?php echo e($text); ?></li>
<?php endforeach; ?>
                </ul>
            </div>

            <div class="instructions-section">
                <h2>Instructions</h2>
                <ol>
                    <?php foreach (explode("\n", $recipe['instructions']) as $step): ?>
                        <li><?php echo e(trim($step)); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>

        <?php if (!empty($recipe['chefs_notes'])): ?>
        <div class="recipe-tips">
            <h2>Chef's Notes</h2>
            <ul>
                <?php foreach (explode("\n", $recipe['chefs_notes']) as $note): ?>
                    <li><?php echo e(trim($note)); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($recipe['calories']) || !empty($recipe['fat']) || !empty($recipe['carbs']) || !empty($recipe['protein'])): ?>
        <div class="nutrition-info">
            <h2>Nutrition Information</h2>
            <div class="nutrition-grid">
                <?php if (!empty($recipe['calories'])): ?>
                <div class="nutrition-item">
                    <span class="nutrition-value"><?php echo e($recipe['calories']); ?></span>
                    <span class="nutrition-label">Calories</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($recipe['fat'])): ?>
                <div class="nutrition-item">
                    <span class="nutrition-value"><?php echo e($recipe['fat']); ?></span>
                    <span class="nutrition-label">Fat</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($recipe['carbs'])): ?>
                <div class="nutrition-item">
                    <span class="nutrition-value"><?php echo e($recipe['carbs']); ?></span>
                    <span class="nutrition-label">Carbs</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($recipe['protein'])): ?>
                <div class="nutrition-item">
                    <span class="nutrition-value"><?php echo e($recipe['protein']); ?></span>
                    <span class="nutrition-label">Protein</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
// Nutrition Warnings Detection
$conflictRules = [
    'vegetarian' => ['beef', 'pork', 'chicken', 'meat', 'bacon', 'sausage', 'turkey', 'ham', 'lamb', 'duck'],
    'vegan' => ['beef', 'pork', 'chicken', 'meat', 'bacon', 'milk', 'butter', 'cheese', 'cream', 'egg', 'honey', 'yogurt'],
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
    'kosher' => [] // Kosher is tricky: manually tagged maybe later
];

$nutritionStatuses = [];

foreach ($conflictRules as $preference => $keywords) {
    $conflict = false;
    foreach ($keywords as $word) {
        if (strpos($ingredientText, $word) !== false) {
            $conflict = true;
            break;
        }
    }
    $nutritionStatuses[$preference] = !$conflict; // true if recipe is safe, false if conflict
}
?>

<div id="nutrition-warnings-wrapper">
    <h2>Nutrition Warnings</h2>
    <div class="nutrition-controls">
    <button class="nutrition-warnings-toggle btn-small" onclick="toggleNutritionWarnings()">
        <span class="chevron-icon">&#9660;</span> Hide Nutrition Warnings
    </button>
    <button class="nutrition-toggle-showall btn-small" onclick="toggleShowAllStatuses()" data-showing="false">
        <span class="chevron-icon">&#9654;</span> Show All Statuses
    </button>
</div>
  <div id="nutrition-warnings-content" class="collapsible open">
    <table class="nutrition-warnings-table">
        <thead>
            <tr>
                <th>Preference</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($nutritionStatuses as $pref => $isSafe): ?>
        <tr class="<?php echo $isSafe ? 'status-row ok-row hidden' : 'status-row conflict-row'; ?>">
    <td data-label="Preference"><?php echo ucfirst(str_replace('-', ' ', $pref)); ?></td>
    <td data-label="Status" class="<?php echo $isSafe ? 'yes' : 'no'; ?>">
        <?php echo $isSafe ? '✅ Okay' : '❌ Conflict'; ?>
    </td>
</tr>
    <?php endforeach; ?>
</tbody>
    </table>
  </div>
</div>
</section>

<!-- Scroll Up Button -->
<button id="scroll-to-ingredients" class="scroll-up-btn" title="Back to ingredients">
  ↑ Back to Ingredients
</button>

<?php if (isset($_SESSION['user_id'])): ?>
  <a href="favorites.php" class="btn">See Saved Recipes</a>
<?php else: ?>
  <a href="login.php" class="btn">See Saved Recipes</a>
<?php endif; ?>

<div id="toast" class="toast"></div>
<div id="tags-container" class="tags"></div>

<script src="js/auth.js"></script>
<script src="js/script.js"></script>
<script src="js/favorites.js"></script>

<script>
function toggleNutritionWarnings() {
    const content = document.getElementById('nutrition-warnings-content');
    const toggleBtn = document.querySelector('.nutrition-warnings-toggle');
    const chevron = toggleBtn.querySelector('.chevron-icon');

    if (content.classList.contains('closed')) {
        content.classList.remove('closed');
        toggleBtn.innerHTML = '<span class="chevron-icon">&#9660;</span> Hide Nutrition Warnings';
    } else {
        content.classList.add('closed');
        toggleBtn.innerHTML = '<span class="chevron-icon rotate">&#9654;</span> Show Nutrition Warnings';
    }
}

function toggleShowAllStatuses() {
    const button = document.querySelector('.nutrition-toggle-showall');
    const chevron = button.querySelector('.chevron-icon');
    const isShowingAll = button.getAttribute('data-showing') === 'true';
    const okRows = document.querySelectorAll('.ok-row');

    okRows.forEach(row => row.classList.toggle('hidden', isShowingAll));

    button.innerHTML = isShowingAll
        ? '<span class="chevron-icon">&#9654;</span> Show All Statuses'
        : '<span class="chevron-icon rotate">&#9660;</span> Hide Okay Statuses';
    button.setAttribute('data-showing', isShowingAll ? 'false' : 'true');
}

const decreaseBtn = document.getElementById('decrease-servings');
const increaseBtn = document.getElementById('increase-servings');
const servingsDisplay = document.getElementById('servings');
const servingSizeDisplay = document.getElementById('serving-size');
const ingredientsList = document.getElementById('ingredients-list');

function animateCount(el) {
  el.classList.add('animate');
  setTimeout(() => el.classList.remove('animate'), 250);
}

if (decreaseBtn && increaseBtn && servingsDisplay) {
    animateCount(servingsDisplay);
animateCount(servingSizeDisplay);
  let currentServings = parseInt(servingsDisplay.textContent);
  const originalServings = currentServings;

  const ingredients = [];
  const quantityElements = ingredientsList.querySelectorAll('.quantity');
  quantityElements.forEach(element => {
    ingredients.push({
      element,
      baseValue: parseFloat(element.getAttribute('data-base'))
    });
  });

  function updateIngredients() {
  ingredients.forEach(ingredient => {
    const newValue = (ingredient.baseValue * currentServings / originalServings).toFixed(2);
    const displayValue = newValue.replace(/\\.0+$/, '').replace(/(\\.\\d*?)0+$/, '$1');

    // Add animation class temporarily
    ingredient.element.classList.add('animate');
    ingredient.element.textContent = displayValue;

    // Remove animation class after animation ends
    setTimeout(() => ingredient.element.classList.remove('animate'), 300);
  });
}

  decreaseBtn.addEventListener('click', () => {
    if (currentServings > 1) {
      currentServings--;
      servingsDisplay.textContent = currentServings;
      servingSizeDisplay.textContent = currentServings;
      updateIngredients();
    }
  });

  increaseBtn.addEventListener('click', () => {
    currentServings++;
    servingsDisplay.textContent = currentServings;
    servingSizeDisplay.textContent = currentServings;
    updateIngredients();
  });
}

const ingredientWords = <?php
$ingredients = array_map(function($line) {
    $clean = trim(preg_replace('/^\d+[\d\/\.\s\-]*(cup|cups|tsp|tbsp|oz|ml|g|grams|kilograms|kg|lb|lbs)?\.?\s*/i', '', $line));
    return preg_replace('/[^a-zA-Z\s\-]/', '', $clean); // Strip punctuation
}, explode("\n", $recipe['ingredients']));
echo json_encode($ingredients);
?>;

document.querySelectorAll('.instructions-section ol li').forEach(step => {
  let html = step.textContent;
  ingredientWords.forEach(word => {
    if (word.length < 3) return; // avoid overmatching short words
    const safeWord = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // escape regex
    const regex = new RegExp(`\\b(${safeWord})\\b`, 'gi');
    html = html.replace(regex, '<strong>$1</strong>');
  });
  step.innerHTML = html;
});

document.addEventListener('DOMContentLoaded', function () {
  const scrollBtn = document.getElementById('scroll-to-ingredients');
  const ingredientsSection = document.querySelector('.ingredients-section');

  window.addEventListener('scroll', function () {
    const scrollY = window.scrollY || window.pageYOffset;
    const instructionsTop = document.querySelector('.instructions-section').offsetTop;

    // Only show button if scrolled past the instructions section
    if (scrollY > instructionsTop - 200) {
      scrollBtn.classList.add('show');
    } else {
      scrollBtn.classList.remove('show');
    }
  });

  scrollBtn.addEventListener('click', function () {
    ingredientsSection.scrollIntoView({ behavior: 'smooth' });
  });
});
</script>
</body>
</html>