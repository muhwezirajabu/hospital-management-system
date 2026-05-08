<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty(trim($input['name'] ?? '')) || empty($input['facilityId'])) {
    echo json_encode(['success' => false, 'error' => 'Name and facilityId required']);
    exit;
}

 $name = trim($input['name']);
 $title = trim($input['title'] ?? '');
 $responsibility = trim($input['responsibility'] ?? '');
 $telephone = trim($input['telephone'] ?? '');
 $facilityId = (int)$input['facilityId'];

 $stmt = $pdo->prepare('INSERT INTO workers (name, title, responsibility, telephone, facilityId) VALUES (?, ?, ?, ?, ?)');
 $stmt->execute([$name, $title, $responsibility, $telephone, $facilityId]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);