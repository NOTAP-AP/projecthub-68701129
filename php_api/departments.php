<?php
require_once __DIR__ . '/condb.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, OPTIONS');
    echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะ GET'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $departments = $conn->query('SELECT id, name, description FROM departments ORDER BY name')->fetchAll();
    $positions = $conn->query(
        'SELECT p.id, p.name, p.department_id, d.name AS department
         FROM positions p
         LEFT JOIN departments d ON d.id = p.department_id
         ORDER BY d.name, p.name'
    )->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => ['departments' => $departments, 'positions' => $positions],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'อ่านข้อมูลแผนกไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
}