<?php
require_once __DIR__ . '/config.php';  // session_start + DB
header('Content-Type: application/json');

// now you can safely read/write $_SESSION
if ($_GET['action'] === 'session') {
    echo json_encode([
      'authenticated' => !empty($_SESSION['user_id'])
      // …etc…
    ]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $ingredients = htmlspecialchars($_POST['ingredients']);
    $instructions = htmlspecialchars($_POST['instructions']);
    $source = htmlspecialchars($_POST['sourceFile']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        INSERT INTO recipes (id, title, image, description, category, time, calories, servings)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        uniqid('recipe_'), // unique ID
        $title,
        $source,
        $instructions,
        'user-uploaded', // category
        30, // default time
        0, // default calories
        1 // default servings
    ]);

    header("Location: import.php?success=1");
    exit;
}
?>