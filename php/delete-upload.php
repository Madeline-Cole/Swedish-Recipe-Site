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

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$uploadId = $_POST['upload_id'] ?? null;

if ($uploadId) {
    $stmt = $pdo->prepare("SELECT * FROM user_uploads WHERE id = ? AND user_id = ?");
    $stmt->execute([$uploadId, $_SESSION['user_id']]);
    $upload = $stmt->fetch();

    if ($upload) {
        // Try to delete local file
        $absolutePath = __DIR__ . '/../' . $upload['path'];
        if (in_array($upload['type'], ['pdf', 'docx']) && file_exists($absolutePath)) {
            unlink($absolutePath);
        }

        // Delete from DB
        $stmt = $pdo->prepare("DELETE FROM user_uploads WHERE id = ? AND user_id = ?");
        $stmt->execute([$uploadId, $_SESSION['user_id']]);
    }
}

header("Location: ../my-uploads.php?deleted=1");
exit;
