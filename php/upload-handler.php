<?php
require_once __DIR__ . '/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir);

$title = '';
$path = '';
$type = '';
$date = date('Y-m-d H:i:s');

// FILE UPLOAD
if (!empty($_FILES['recipeFile']['name'])) {
    $file = $_FILES['recipeFile'];
    $fileName = basename($file['name']);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $storedName = uniqid() . '.' . $ext;
    $dest = $uploadDir . $storedName;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $title = pathinfo($fileName, PATHINFO_FILENAME);
        $path = 'uploads/' . $storedName;
        $type = 'file';
    }
}
// BOOKMARK LINK
elseif (!empty($_POST['recipeUrl']) && !empty($_POST['bookmarkTitle'])) {
    $title = htmlspecialchars($_POST['bookmarkTitle']);
    $path = htmlspecialchars($_POST['recipeUrl']);
    $type = 'bookmark';
} else {
    header("Location: ../import.php?error=missing_data");
    exit;
}

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO user_uploads (user_id, title, path, type, created_at) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $title, $path, $type, $date]);

header("Location: ../my-uploads.php?success=1");
exit;