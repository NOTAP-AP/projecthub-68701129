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
    $summary = [
        'total_employees' => (int) $conn->query('SELECT COUNT(*) FROM employees')->fetchColumn(),
        'active_employees' => (int) $conn->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn(),
        'pending_leaves' => (int) $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'")->fetchColumn(),
        'checked_in_today' => (int) $conn->query(
            'SELECT COUNT(*) FROM attendances WHERE attendance_date = CURRENT_DATE() AND check_in IS NOT NULL'
        )->fetchColumn(),
    ];

    echo json_encode(['success' => true, 'data' => $summary], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'อ่านข้อมูลแดชบอร์ดไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
}