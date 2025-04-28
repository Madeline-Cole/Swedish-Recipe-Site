<?php
require_once __DIR__ . '/config.php';  // session_start + DB
header('Content-Type: application/json');

$slug = $_GET['slug'] ?? null;
if (!$slug && !empty($_GET['id'])) {
  // lookup slug by id
  $row = $pdo->prepare("SELECT slug FROM recipes WHERE id=?");
  $row->execute([ $_GET['id'] ]);
  $slug = $row->fetchColumn();
}

// now you can safely read/write $_SESSION
if ($_GET['action'] === 'session') {
    echo json_encode([
      'authenticated' => !empty($_SESSION['user_id'])
      // …etc…
    ]);
    exit;
}

// --- Handle action ---
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'session':
      echo json_encode(['authenticated'=> !empty($_SESSION['user_id'])]);
      break;
      exit;
  
    case 'list':
      listRecipes($pdo);
      break;
      exit;
  
    case 'get':
      getRecipe($pdo);
      break;
      exit;
  
    default:
      http_response_code(400);
      echo json_encode(['error'=>'Invalid action']);
      
  }

// --- List all recipes (optionally filter by category and/or tag) ---
function listRecipes($pdo) {
    $category = $_GET['category'] ?? '';
    $tag      = $_GET['tag']      ?? '';
    $sql = "SELECT id, title, image, description, category, time, calories, servings, slug
              FROM recipes";
    $params = [];
    if ($category) {
      $sql .= " WHERE category = ?";
      $params[] = $category;
    }
    if ($tag) {
      $sql .= empty($params)
        ? " WHERE tags LIKE ?"
        : " AND tags LIKE ?";
      $params[] = "%$tag%";
    }
    $sql .= " ORDER BY title ASC";
  
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
  }
  
  function getRecipe($pdo) {
    global $slug;
    if (!$slug) {
      http_response_code(400);
      echo json_encode(['error'=>'Missing slug or id']);
      exit;
    }
    $stmt = $pdo->prepare("
      SELECT * FROM recipes WHERE slug = ?
    ");
    $stmt->execute([ $slug ]);
    $recipe = $stmt->fetch();
    if ($recipe) {
      echo json_encode($recipe);
    } else {
      http_response_code(404);
      echo json_encode(['error'=>'Recipe not found']);
    }
  }
?>