<?php
require_once 'config.php';

// ====== AFRICA'S TALKING CONFIG ======
$AT_USERNAME = 'sandbox';         // Change to your real username when going live
$AT_API_KEY  = 'atsk_7373ac9ec479bcb8b46331abd4dd3853f0f2e3d9ee8366ece521779fb81f28dc1d2ff5fe'; // Paste your Africa's Talking API key here
$AT_SENDER   = '';                 // Leave empty for sandbox; set your shortcode for live
// =====================================

$data = json_decode(file_get_contents('php://input'), true);
$username = isset($data['username']) ? trim($data['username']) : '';
$pin      = isset($data['pin'])      ? trim($data['pin'])      : '';
$phone    = isset($data['phone'])    ? trim($data['phone'])    : '';

if (!$username || !$pin || !$phone) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

// Normalize phone: convert 07XXXXXXXX to +2567XXXXXXXX
$phone = preg_replace('/\s+/', '', $phone);
if (preg_match('/^07\d{8}$/', $phone)) {
    $phone = '+256' . substr($phone, 1);
}

if (!preg_match('/^\+256\d{9}$/', $phone)) {
    echo json_encode(['success' => false, 'error' => 'Invalid phone number format']);
    exit;
}

$message = "Your UPHS password reset PIN is: $pin. It expires in 5 minutes. Do not share it.";

// Send via Africa's Talking
$url = 'https://api.sandbox.africastalking.com/version1/messaging'; // Use https://api.africastalking.com/version1/messaging for live

$postData = [
    'username' => $AT_USERNAME,
    'to'       => $phone,
    'message'  => $message,
];
if ($AT_SENDER) $postData['from'] = $AT_SENDER;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'apiKey: ' . $AT_API_KEY,
    'Content-Type: application/x-www-form-urlencoded',
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'error' => 'Network error: ' . $curlError]);
    exit;
}

$result = json_decode($response, true);

// Africa's Talking returns SMSMessageData with Recipients
if (
    isset($result['SMSMessageData']['Recipients'][0]['statusCode']) &&
    $result['SMSMessageData']['Recipients'][0]['statusCode'] == 101
) {
    // Log to DB
    try {
        $stmt = $pdo->prepare("INSERT INTO audit (action, record, details, username, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['Password Reset PIN Sent', $username, 'PIN sent to ' . $phone, $username, 'System']);
    } catch (Exception $e) { /* ignore audit failure */ }

    echo json_encode(['success' => true, 'message' => 'PIN sent successfully']);
} else {
    $errMsg = $result['SMSMessageData']['Recipients'][0]['status'] ?? $response;
    echo json_encode(['success' => false, 'error' => 'SMS failed: ' . $errMsg]);
}
?>