<?php
require_once __DIR__ . '/condb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะ POST'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'รูปแบบข้อมูลไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    exit;
}

$firstName = trim((string) ($data['first_name'] ?? ''));
$lastName = trim((string) ($data['last_name'] ?? ''));
$email = trim((string) ($data['email'] ?? ''));
$username = trim((string) ($data['username'] ?? ''));
$password = $data['password'] ?? '';

if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || !is_string($password)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อ นามสกุล อีเมล ชื่อผู้ใช้ และรหัสผ่าน'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100 ||
    mb_strlen($username) > 80 || mb_strlen($email) > 190) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ข้อมูลบางช่องยาวเกินกำหนด'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn->beginTransaction();
    $employeeCode = 'EMP-' . strtoupper(bin2hex(random_bytes(4)));
    $employee = $conn->prepare(
        "INSERT INTO employees (employee_code, first_name, last_name, email, hire_date, status)
         VALUES (?, ?, ?, ?, CURRENT_DATE(), 'Active')"
    );
    $employee->execute([$employeeCode, $firstName, $lastName, $email]);
    $employeeId = (int) $conn->lastInsertId();

    $user = $conn->prepare(
        'INSERT INTO users (employee_id, username, password_hash, role, is_active)
         VALUES (?, ?, ?, \'employee\', 1)'
    );
    $user->execute([$employeeId, $username, password_hash($password, PASSWORD_DEFAULT)]);
    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'สมัครพนักงานสำเร็จ',
        'employee_code' => $employeeCode,
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'อีเมลหรือชื่อผู้ใช้นี้ถูกใช้แล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสมัครพนักงาน'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสมัครพนักงาน'], JSON_UNESCAPED_UNICODE);
}