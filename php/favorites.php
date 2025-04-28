<?php
// php/favorites.php

// 1) start session & pull in your $pdo
require_once __DIR__ . '/config.php';  // must call session_start() and set up $pdo

header('Content-Type: application/json');

// 2) session check
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'Unauthorized']);
  exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
  case 'list':
    $stmt = $pdo->prepare("
      SELECT r.id, r.title, r.image, r.description, r.category, r.time, r.calories, r.servings, r.slug
      FROM favorites f
      JOIN recipes r ON r.id = f.recipe_id
      WHERE f.user_id = ?
    ");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'toggle':
    $recipe_id = $_POST['recipe_id'] ?? '';
    if (! $recipe_id) {
      http_response_code(400);
      echo json_encode(['error'=>'Missing recipe_id']);
      exit;
    }

    // — properly check if it exists
    $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND recipe_id=?");
    $check->execute([$user_id, $recipe_id]);
    $exists = (bool) $check->fetchColumn();

    if ($exists) {
      $del = $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND recipe_id=?");
      $del->execute([$user_id, $recipe_id]);
      $status = 'removed';
    } else {
      $ins = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?,?)");
      $ins->execute([$user_id, $recipe_id]);
      $status = 'added';
    }

    // — now get the new count correctly
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=?");
    $cnt->execute([$user_id]);
    $favorite_count = (int) $cnt->fetchColumn();

    echo json_encode(['status'=>$status, 'favorite_count'=>$favorite_count]);
    break;

  default:
    http_response_code(400);
    echo json_encode(['error'=>'Invalid action']);
    break;
}
