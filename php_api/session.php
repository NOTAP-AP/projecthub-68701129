<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะ GET'], JSON_UNESCAPED_UNICODE);
    exit;
}

session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => (int) $_SESSION['user_id'],
        'role' => $_SESSION['role'] ?? '',
    ],
], JSON_UNESCAPED_UNICODE);
