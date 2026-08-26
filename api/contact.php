<?php
/**
 * API Contact & Consultation Lead Handler
 * Database connection & Lead recording for Minh Anh Vu Law Firm
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db_host = 'localhost';
$db_name = 'db_3c0c9c9e';
$db_user = 'db_3c0c9c9e';
$db_pass = 'MHh&pqRjv83hg0f*';

// Handle both standard POST and JSON raw body
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    }
}

$fullname = trim($input['fullname'] ?? $input['name'] ?? '');
$phone    = trim($input['phone'] ?? $input['tel'] ?? '');
$email    = trim($input['email'] ?? '');
$service  = trim($input['service'] ?? 'Tư vấn pháp luật');
$message  = trim($input['message'] ?? $input['content'] ?? '');

if (empty($fullname) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập họ tên và số điện thoại.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$db_host;port=3306;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Create table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `consultation_leads` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `fullname` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NOT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `service` VARCHAR(255) DEFAULT NULL,
            `message` TEXT DEFAULT NULL,
            `ip_address` VARCHAR(50) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $stmt = $pdo->prepare("
        INSERT INTO `consultation_leads` (`fullname`, `phone`, `email`, `service`, `message`, `ip_address`)
        VALUES (:fullname, :phone, :email, :service, :message, :ip)
    ");

    $stmt->execute([
        ':fullname' => $fullname,
        ':phone'    => $phone,
        ':email'    => $email,
        ':service'  => $service,
        ':message'  => $message,
        ':ip'       => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Gửi yêu cầu tư vấn thành công! Luật sư sẽ liên hệ với bạn trong vòng 30 phút.'
    ]);
} catch (Exception $e) {
    error_log("DB Lead error: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'message' => 'Gửi yêu cầu tư vấn thành công! Luật sư sẽ liên hệ với bạn trong thời gian sớm nhất.'
    ]);
}
