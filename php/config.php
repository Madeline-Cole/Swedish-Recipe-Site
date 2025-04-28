<?php
// php/config.php

// 1) ensure sessions are running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) build your PDO *once* here
$host    = 'db5017678985.hosting-data.io';
$db      = 'dbs14138606';
$user    = 'dbu1191002';
$pass    = '2112snappY!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  http_response_code(500);
  exit(json_encode(['error' => 'Database connection failed']));
}

// 3) any other global setup…
