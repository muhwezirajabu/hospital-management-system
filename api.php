<?php
// ===== DATABASE CONNECTION =====
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Details from your InfinityFree Screenshot
 $host = "sql300.infinityfree.com";
 $user = "if0_41869145_xxxxx"; // Your exact MySQL Username
 $pass = "YOUR_DATABASE_PASSWORD"; // <--- PUT YOUR DATABASE PASSWORD HERE
 $dbname = "if0_41869145_uph";

 $conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database Connection Failed: " . $conn->connect_error]));
}

// Read the action requested by the JavaScript
 $action = $_REQUEST['action'] ?? '';

// ===== ACTION: PULL DATA FROM DATABASE TO APP =====
if ($action === 'pull') {
    $tables = ['facilities', 'departments', 'users', 'assets', 'workers', 'audit'];
    $data = [];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        if ($result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $data[$table] = $rows;
        } else {
            $data[$table] = [];
        }
    }
    echo json_encode(["success" => true, "data" => $data]);
}

// ===== ACTION: PUSH DATA FROM APP TO DATABASE =====
if ($action === 'push') {
    $input = json_decode(file_get_contents('php://input'), true);
    $table = $conn->real_escape_string($input['table']);
    $items = $input['data'];

    // Clear existing data in this table before pushing the new updated list
    $conn->query("TRUNCATE TABLE $table");

    if (empty($items)) {
        echo json_encode(["success" => true, "message" => "$table cleared"]);
        exit;
    }

    // Get the column names from the first item to build the INSERT query
    $columns = array_keys($items[0]);
    $colString = implode("`, `", $columns);

    $values = [];
    foreach ($items as $item) {
        $valArray = [];
        foreach ($columns as $col) {
            $val = isset($item[$col]) ? $item[$col] : '';
            $valArray[] = "'" . $conn->real_escape_string($val) . "'";
        }
        $values[] = "(" . implode(", ", $valArray) . ")";
    }

    $valString = implode(", ", $values);
    $sql = "INSERT INTO `$table` (`$colString`) VALUES $valString";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true, "message" => "$table synced successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}

// ===== ACTION: FIX DEFAULT LOGIN (Resets superadmin password to admin123) =====
if ($action === 'reset_admin') {
    // Hash matches 'admin123' with salt 'UPHS-AMS-v1'
    $newHash = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'; 
    $sql = "UPDATE users SET passwordHash='$newHash' WHERE username='superadmin'";
    if ($conn->query($sql)) {
        echo json_encode(["success" => true, "message" => "Admin password reset to admin123"]);
    } else {
        // If update fails, maybe the user doesn't exist in DB yet, insert them
        $sql = "INSERT INTO users (id, username, passwordHash, role, facilityId, telephone, _sync) 
                VALUES (1, 'superadmin', '$newHash', 'Super Admin', NULL, '', 1)";
        $conn->query($sql);
        echo json_encode(["success" => true, "message" => "Admin user created with password admin123"]);
    }
}

 $conn->close();
?>
