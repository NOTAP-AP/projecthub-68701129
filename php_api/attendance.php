<?php
require_once __DIR__ . '/condb.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method === 'GET') {
        $stmt = $conn->query(
            'SELECT a.id, a.employee_id, e.employee_code, e.first_name, e.last_name,
                    a.attendance_date, a.check_in, a.check_out, a.status, a.note
             FROM attendances a JOIN employees e ON e.id = a.employee_id
             ORDER BY a.attendance_date DESC, a.id DESC LIMIT 200'
        );
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        if (!is_array($data) || !in_array($action, ['check_in', 'check_out'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'ระบุ action เป็น check_in หรือ check_out'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $employeeId = filter_var($data['employee_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$employeeId) {
            $stmt = $conn->prepare('SELECT employee_id FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $employeeId = $stmt->fetchColumn();
        }
        if (!$employeeId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'บัญชีนี้ยังไม่ได้ผูกกับพนักงาน กรุณาระบุ employee_id'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $date = date('Y-m-d');
        if ($action === 'check_in') {
            $stmt = $conn->prepare(
                "INSERT INTO attendances (employee_id, attendance_date, check_in, status)
                 VALUES (?, ?, CURRENT_TIME(), 'Present')
                 ON DUPLICATE KEY UPDATE check_in = COALESCE(check_in, CURRENT_TIME())"
            );
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO attendances (employee_id, attendance_date, check_out, status)
                 VALUES (?, ?, CURRENT_TIME(), \'Present\')
                 ON DUPLICATE KEY UPDATE check_out = CURRENT_TIME()'
            );
        }
        $stmt->execute([$employeeId, $date]);
        echo json_encode(['success' => true, 'message' => $action === 'check_in' ? 'บันทึกเวลาเข้างานแล้ว' : 'บันทึกเวลาออกงานแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Allow: GET, POST, OPTIONS');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'ไม่รองรับ HTTP method นี้'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ประมวลผลข้อมูลลงเวลาไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
}