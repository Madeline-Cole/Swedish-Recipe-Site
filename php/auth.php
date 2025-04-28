<?php
require_once __DIR__ . '/config.php';  // session_start + DB
header('Content-Type: application/json');

// --- HANDLE REQUEST ---
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister($pdo);
        break;
    case 'login':
        handleLogin($pdo);
        break;
    case 'session':
        handleSession();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// --- HANDLERS ---

function handleRegister($pdo) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        return;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hash]);
    $user_id = $pdo->lastInsertId();

    $_SESSION['user_id'] = $user_id;

    echo json_encode(['message' => 'Registered successfully', 'user' => ['id' => $user_id, 'name' => $name, 'email' => $email]]);
}

function handleLogin($pdo) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing email or password']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }

    $_SESSION['user_id'] = $user['id'];

    echo json_encode(['message' => 'Login successful', 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]]);
}

function handleSession() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['authenticated' => false]);
        return;
    }

    // Optionally fetch user details from DB here
    echo json_encode(['authenticated' => true, 'user_id' => $_SESSION['user_id']]);
}

function handleLogout() {
    session_unset();
    session_destroy();
    echo json_encode(['message' => 'Logged out']);
}
?>