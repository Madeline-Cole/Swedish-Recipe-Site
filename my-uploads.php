<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/partials/nav.php';
?>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user_uploads WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$uploads = $stmt->fetchAll();

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
  <title>My Uploads - Swedish Recipes</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<section class="profile-container">
  <div class="container">
    <h1>My Uploads</h1>

    <?php if (empty($uploads)): ?>
      <p>You haven’t uploaded any recipes yet.</p>
    <?php else: ?>
      <?php foreach ($uploads as $upload): ?>
        <div class="upload-card">
          <h3><?php echo htmlspecialchars($upload['title']); ?></h3>

          <?php if (in_array($upload['type'], ['pdf', 'docx'])): ?>
            <a href="#" class="btn-small open-upload" data-id="<?php echo $upload['id']; ?>">
  View <?php echo strtoupper($upload['type']); ?>
</a>
          <?php elseif ($upload['type'] === 'link' || $upload['type'] === 'bookmark'): ?>
            <a href="<?php echo htmlspecialchars($upload['path']); ?>" target="_blank" class="btn-small">
              Visit Link
            </a>
          <?php endif; ?>

          <form method="POST" action="delete-upload.php" onsubmit="return confirm('Delete this item?');" style="display:inline;">
            <input type="hidden" name="upload_id" value="<?php echo $upload['id']; ?>">
            <button type="submit" class="btn-small danger">Delete</button>
          </form>

          <p>
            <em><?php echo ucfirst($upload['type']); ?> uploaded on 
              <?php echo date('F j, Y', strtotime($upload['created_at'])); ?>
            </em>
          </p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'partials/footer.php'; ?>

<div id="upload-modal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <iframe id="upload-frame" src="" frameborder="0" style="width:100%; height:80vh;"></iframe>
  </div>
</div>


<script src="js/script.js"></script>

<script>
document.querySelectorAll('.open-upload').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    const fileId = this.getAttribute('data-id');
    const frame = document.getElementById('upload-frame');
    frame.src = `view-upload.php?id=${fileId}`;
    document.getElementById('upload-modal').style.display = 'block';
  });
});

document.querySelector('.modal .close-btn').addEventListener('click', function () {
  document.getElementById('upload-modal').style.display = 'none';
  document.getElementById('upload-frame').src = '';
});

window.addEventListener('click', function (e) {
  const modal = document.getElementById('upload-modal');
  if (e.target === modal) {
    modal.style.display = 'none';
    document.getElementById('upload-frame').src = '';
  }
});
</script>

</body>
</html>