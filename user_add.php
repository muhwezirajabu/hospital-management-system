<?php
require_once 'config.php';

 $input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty(trim($input['username'] ?? '')) || empty($input['passwordHash'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Username and password required']);
    exit;
}

 $username = trim($input['username']);
 $passwordHash = $input['passwordHash'];
 $role = $input['role'] ?? 'User';
 $facilityId = !empty($input['facilityId']) ? (int)$input['facilityId'] : null;

 $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
 $stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Username already exists']);
    exit;
}

 $stmt = $pdo->prepare('INSERT INTO users (username, passwordHash, role, facilityId) VALUES (?, ?, ?, ?)');
 $stmt->execute([$username, $passwordHash, $role, $facilityId]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);