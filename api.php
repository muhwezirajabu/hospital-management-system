<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ====== YOUR DATABASE DETAILS ======
 $host = "sql300.infinityfree.com";
 $user = "if0_41869145_xxxxx"; // YOUR USERNAME
 $pass = "YOUR_DATABASE_PASSWORD"; // YOUR PASSWORD
 $dbname = "if0_41869145_uph";
// ===================================

 $conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database Connection failed: " . $conn->connect_error]));
}

 $action = $_POST['action'] ?? '';
 $json_data = isset($_POST['json_data']) ? json_decode($_POST['json_data'], true) : [];

function respond($success, $id = null, $error = "") {
    $res = ["success" => $success];
    if ($id !== null) $res["id"] = $id;
    if ($error) $res["error"] = $error;
    echo json_encode($res);
    exit;
}

if ($action === 'add_facility') {
    $name = $conn->real_escape_string($json_data['name'] ?? '');
    $sql = "INSERT INTO facilities (name) VALUES ('$name')";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

if ($action === 'add_department') {
    $name = $conn->real_escape_string($json_data['name'] ?? '');
    $fid = intval($json_data['facilityId'] ?? 0);
    $sql = "INSERT INTO departments (name, facilityId) VALUES ('$name', $fid)";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

if ($action === 'add_user') {
    $username = $conn->real_escape_string($json_data['username'] ?? '');
    $passwordHash = $conn->real_escape_string($json_data['passwordHash'] ?? '');
    $role = $conn->real_escape_string($json_data['role'] ?? '');
    $fid = !empty($json_data['facilityId']) ? intval($json_data['facilityId']) : 'NULL';
    $telephone = $conn->real_escape_string($json_data['telephone'] ?? '');
    $sql = "INSERT INTO users (username, passwordHash, role, facilityId, telephone) VALUES ('$username', '$passwordHash', '$role', $fid, '$telephone')";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

if ($action === 'add_worker') {
    $name = $conn->real_escape_string($json_data['name'] ?? '');
    $title = $conn->real_escape_string($json_data['title'] ?? '');
    $resp = $conn->real_escape_string($json_data['responsibility'] ?? '');
    $tel = $conn->real_escape_string($json_data['telephone'] ?? '');
    $fid = intval($json_data['facilityId'] ?? 0);
    $sql = "INSERT INTO workers (name, title, responsibility, telephone, facilityId) VALUES ('$name', '$title', '$resp', '$tel', $fid)";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

if ($action === 'add_asset') {
    $ref = $conn->real_escape_string($json_data['refNumber'] ?? '');
    $name = $conn->real_escape_string($json_data['name'] ?? '');
    $type = $conn->real_escape_string($json_data['type'] ?? '');
    $fid = intval($json_data['facilityId'] ?? 0);
    $did = intval($json_data['deptId'] ?? 0);
    $room = $conn->real_escape_string($json_data['room'] ?? '');
    $model = $conn->real_escape_string($json_data['model'] ?? '');
    $serial = $conn->real_escape_string($json_data['serial'] ?? '');
    $mfr = $conn->real_escape_string($json_data['manufacturer'] ?? '');
    $qty = intval($json_data['qty'] ?? 1);
    $date = $conn->real_escape_string($json_data['dateEntered'] ?? '');
    $cond = $conn->real_escape_string($json_data['conditionVal'] ?? '');
    $status = $conn->real_escape_string($json_data['status'] ?? '');
    $by = $conn->real_escape_string($json_data['enteredBy'] ?? '');
    
    $sql = "INSERT INTO assets (refNumber, name, type, facilityId, deptId, room, model, serial, manufacturer, qty, dateEntered, conditionVal, status, enteredBy) 
            VALUES ('$ref', '$name', '$type', $fid, $did, '$room', '$model', '$serial', '$mfr', $qty, '$date', '$cond', '$status', '$by')";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

if ($action === 'add_audit') {
    $action_name = $conn->real_escape_string($json_data['action'] ?? '');
    $record = $conn->real_escape_string($json_data['record'] ?? '');
    $details = $conn->real_escape_string($json_data['details'] ?? '');
    $username = $conn->real_escape_string($json_data['username'] ?? '');
    $role = $conn->real_escape_string($json_data['role'] ?? '');
    $sql = "INSERT INTO audit (action, record, details, username, role) VALUES ('$action_name', '$record', '$details', '$username', '$role')";
    if ($conn->query($sql)) respond(true, $conn->insert_id);
    else respond(false, null, $conn->error);
}

respond(false, null, "Invalid action");
?>
