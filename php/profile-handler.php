<?php
// php/profile-handler.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// 1) bootstraps session + $pdo
require_once __DIR__ . '/config.php';

// 2) require login
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Not logged in']));
}

$uid = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'session':
            echo json_encode(['authenticated' => true]);
            break;

        case 'load':
            $stmt = $pdo->prepare("SELECT name, email, bio, preferences FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            echo json_encode($stmt->fetch());
            break;

        case 'favorites':
            $stmt = $pdo->prepare("
              SELECT r.id, r.title, r.image, r.description, r.time, r.servings, r.slug
              FROM favorites f
              JOIN recipes r ON f.recipe_id = r.id
              WHERE f.user_id = ?
            ");
            $stmt->execute([$uid]);
            echo json_encode($stmt->fetchAll());
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid GET action']);
    }
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'update-preferences':
            $prefs = $_POST['preferences'] ?? '[]';
            $stmt = $pdo->prepare("UPDATE users SET preferences = ? WHERE id = ?");
            $stmt->execute([$prefs, $uid]);
            echo json_encode(['success' => true]);
            break;

        case 'update-profile':
            $name = trim($_POST['name'] ?? '');
            $bio  = trim($_POST['bio'] ?? '');
            if ($name === '') {
                echo json_encode(['success' => false, 'error' => 'Name cannot be empty']);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
                $stmt->execute([$name, $bio, $uid]);
                echo json_encode(['success' => true]);
            }
            break;

        case 'change-password':
            $cp = $_POST['current-password'] ?? '';
            $np = $_POST['new-password']     ?? '';
            $cn = $_POST['confirm-new-password'] ?? '';
            if (!$cp || !$np || $np !== $cn) {
                echo json_encode(['success'=>false,'error'=>'Passwords do not match.']);
                break;
            }
            if (strlen($np)<12
             || !preg_match('/[A-Z]/',$np)
             || !preg_match('/\d/',$np)) {
                echo json_encode(['success'=>false,'error'=>'Password must be at least 12 chars, contain upper case & a number.']);
                break;
            }
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($cp, $user['password'])) {
                echo json_encode(['success'=>false,'error'=>'Current password is incorrect.']);
                break;
            }
            $hp = password_hash($np, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hp, $uid]);
            echo json_encode(['success'=>true]);
            break;

        case 'upload-avatar':
            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success'=>false,'error'=>'File upload failed.']);
                break;
            }
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($_FILES['avatar']['type'],$allowed)) {
                echo json_encode(['success'=>false,'error'=>'Only JPG, PNG, GIF & WEBP allowed.']);
                break;
            }
            $dir = __DIR__ . '/../images/avatars/';
            if (!is_dir($dir)) mkdir($dir,0755,true);
            $fn = "user_{$uid}_" . time() . ".webp";
            $path = $dir . $fn;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'],$path)) {
                $url = "images/avatars/{$fn}";
                echo json_encode(['success'=>true,'avatarUrl'=>$url]);
            } else {
                echo json_encode(['success'=>false,'error'=>'Failed to save file.']);
            }
            break;

        case 'delete-account':
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            session_destroy();
            echo json_encode(['success'=>true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error'=>'Invalid POST action']);
    }
    exit;
}

// anything else:
http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
exit;
