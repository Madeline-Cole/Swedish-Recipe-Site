<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/partials/nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Import Recipe - Swedish Recipes</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/import.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<section class="import-recipe">
  <div class="container">
    <h2>Import Your Recipe</h2>
    <form action="php/upload-handler.php" method="POST" enctype="multipart/form-data">
      <div class="import-options">
        <label for="recipeFile">Upload a PDF or DOCX file</label>
        <input type="file" name="recipeFile" id="recipeFile" accept=".pdf,.docx">

        <label for="recipeUrl">Or save a recipe link (bookmark)</label>
        <input type="url" name="recipeUrl" id="recipeUrl" placeholder="https://example.com/recipe">
        <input type="text" name="bookmarkTitle" placeholder="Recipe Title for Bookmark">

        <button class="btn" type="submit">Save</button>
      </div>
    </form>
  </div>
</section>

<?php include 'partials/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>