<?php
require_once __DIR__ . '/php/config.php';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM recipes");
    echo "✅ Connected. Found " . $stmt->fetchColumn() . " recipes.";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
//paste this into browser to test: https://swedish-recipes.madelinecole.com/test-db.php