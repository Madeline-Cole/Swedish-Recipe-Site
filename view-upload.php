<?php require_once __DIR__ . '/php/config.php'
include 'partials/nav.php';?>
<?php

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "No file selected.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user_uploads WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$file = $stmt->fetch();

if (!$file || !in_array($file['type'], ['pdf', 'docx'])) {
    echo "Invalid file.";
    exit;
}

$fileUrl = htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/' . $file['path']);

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
  <title>View File - <?php echo htmlspecialchars($file['title']); ?></title>
  <style>
    html, body {
      margin: 0;
      height: 100%;
      overflow: hidden;
      font-family: sans-serif;
    }
    iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
    .fallback {
      padding: 2rem;
      text-align: center;
    }
  </style>
</head>
<body>

<?php if ($file['type'] === 'pdf'): ?>
  <iframe src="<?php echo $fileUrl; ?>"></iframe>
<?php elseif ($file['type'] === 'docx'): ?>
  <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo urlencode($fileUrl); ?>"></iframe>
<?php else: ?>
  <div class="fallback">
    <p>Sorry, this file type is not supported for inline viewing.</p>
    <a href="<?php echo $fileUrl; ?>" target="_blank">Download File</a>
  </div>
<?php endif; ?>

<script src="js/script.js"></script>

</body>
</html>