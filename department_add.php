<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty(trim($input['name'] ?? '')) || empty($input['facilityId'])) {
    echo json_encode(['success' => false, 'error' => 'Name and facilityId required']);
    exit;
}

 $name = trim($input['name']);
 $facilityId = (int)$input['facilityId'];

 $stmt = $pdo->prepare('SELECT id FROM facilities WHERE id = ?');
 $stmt->execute([$facilityId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Facility not found']);
    exit;
}

 $stmt = $pdo->prepare('INSERT INTO departments (name, facilityId) VALUES (?, ?)');
 $stmt->execute([$name, $facilityId]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);