<?php
require_once __DIR__ . '/config.php';

$uploadDir = 'uploads/';
$uploadedFile = '';
$recipeUrl = '';
$defaultTitle = '';
$defaultIngredients = '';
$defaultInstructions = '';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (isset($_FILES['recipeFile']) && $_FILES['recipeFile']['error'] === 0) {
        $fileTmpPath = $_FILES['recipeFile']['tmp_name'];
        $fileName = basename($_FILES['recipeFile']['name']);
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $uploadedFile = $destPath;

            // Try to extract dummy data (or leave blank for user input)
            $defaultTitle = pathinfo($fileName, PATHINFO_FILENAME);
        }
    }

    if (!empty($_POST['recipeUrl'])) {
        $recipeUrl = htmlspecialchars($_POST['recipeUrl']);
        $defaultTitle = "Imported Recipe";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Imported Recipe</title>
    <link rel="stylesheet" href="import.css">
</head>
<body>
<div class="container">
    <h2>Edit Your Imported Recipe</h2>

    <form action="save-recipe.php" method="POST">
        <div class="recipe-card">
            <div class="recipe-image">
                <img src="images/placeholder-recipe.jpg" alt="Recipe Image">
            </div>
            <div class="recipe-content">
                <label for="title"><strong>Recipe Title</strong></label>
                <input type="text" name="title" id="title" value="<?php echo $defaultTitle; ?>" required>

                <label for="ingredients"><strong>Ingredients</strong></label>
                <textarea name="ingredients" id="ingredients" rows="6" placeholder="e.g. 2 eggs, 1 cup flour, ..."><?php echo $defaultIngredients; ?></textarea>

                <label for="instructions"><strong>Instructions</strong></label>
                <textarea name="instructions" id="instructions" rows="8" placeholder="Step-by-step instructions..."><?php echo $defaultInstructions; ?></textarea>

                <input type="hidden" name="sourceFile" value="<?php echo $uploadedFile ?: $recipeUrl; ?>">

                <button class="btn" type="submit">Save to My Recipes</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
