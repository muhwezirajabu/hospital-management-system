<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty(trim($input['name'] ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'Facility name required']);
    exit;
}

 $name = trim($input['name']);

 $stmt = $pdo->prepare('SELECT id FROM facilities WHERE name = ?');
 $stmt->execute([$name]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Facility already exists']);
    exit;
}

 $stmt = $pdo->prepare('INSERT INTO facilities (name) VALUES (?)');
 $stmt->execute([$name]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);