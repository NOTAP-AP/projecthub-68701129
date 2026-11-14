<?php
header('Content-Type: application/json; charset=utf-8');

$allowedOrigins = [
    'http://localhost:8080',
    'http://127.0.0.1:8080',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = getenv('PROJECTHUB_DB_HOST') ?: '127.0.0.1';
$username = getenv('PROJECTHUB_DB_USER') ?: 'root';
$password = getenv('PROJECTHUB_DB_PASSWORD') ?: '';
$dbname = getenv('PROJECTHUB_DB_NAME') ?: 'project_hub';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เชื่อมต่อฐานข้อมูลไม่ได้ กรุณาตรวจสอบ MySQL และการตั้งค่า project_hub',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}