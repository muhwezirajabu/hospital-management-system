<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty(trim($input['refNumber'] ?? '')) || empty(trim($input['name'] ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'RefNumber and name required']);
    exit;
}

 $refNumber = trim($input['refNumber']);
 $name = trim($input['name']);

 $stmt = $pdo->prepare('SELECT id FROM assets WHERE refNumber = ?');
 $stmt->execute([$refNumber]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Reference number already exists']);
    exit;
}

 $stmt = $pdo->prepare('INSERT INTO assets (refNumber, name, type, facilityId, deptId, room, model, serial, manufacturer, qty, dateEntered, condition_status, status, enteredBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
 $stmt->execute([
    $refNumber,
    $name,
    $input['type'] ?? null,
    (int)($input['facilityId'] ?? 0),
    (int)($input['deptId'] ?? 0),
    $input['room'] ?? null,
    $input['model'] ?? null,
    $input['serial'] ?? null,
    $input['manufacturer'] ?? null,
    (int)($input['qty'] ?? 1),
    $input['dateEntered'] ?? date('Y-m-d'),
    $input['conditionVal'] ?? 'New',
    $input['status'] ?? 'New',
    $input['enteredBy'] ?? null
]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);