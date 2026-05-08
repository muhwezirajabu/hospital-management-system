<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['action'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Action required']);
    exit;
}

 $stmt = $pdo->prepare('INSERT INTO audit (action, record, details, username, role) VALUES (?, ?, ?, ?, ?)');
 $stmt->execute([
    $input['action'],
    $input['record'] ?? null,
    $input['details'] ?? null,
    $input['username'] ?? null,
    $input['role'] ?? null
]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);