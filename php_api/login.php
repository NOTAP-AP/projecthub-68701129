<?php
require_once __DIR__ . '/condb.php';

try {
    $userCount = $conn->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ((int) $userCount === 0) {
        $defaultAdmin = $conn->prepare(
            "INSERT INTO users (username, password_hash, role, is_active)
             VALUES (?, ?, 'admin', 1)"
        );
        $defaultAdmin->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
} catch (PDOException $e) {
    error_log('Seed admin user failed: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะ POST'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['username'], $data['password']) ||
    !is_string($data['username']) || !is_string($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], JSON_UNESCAPED_UNICODE);
    exit;
}

$portal = $data['portal'] ?? 'employee';
if (!is_string($portal) || !in_array($portal, ['admin', 'employee'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ประเภทบัญชีไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.password_hash, u.role, u.employee_id,
                e.employee_code, e.first_name, e.last_name, e.email
         FROM users u
         LEFT JOIN employees e ON e.id = u.employee_id
         WHERE u.is_active = 1
           AND (u.username = :username OR e.employee_code = :employee_code OR e.email = :email)
         LIMIT 1'
    );
    $stmt->execute([
        'username' => $username,
        'employee_code' => $username,
        'email' => $username,
    ]);
    $user = $stmt->fetch();

    $roleAllowed = $user && (
        ($portal === 'admin' && $user['role'] === 'admin') ||
        ($portal === 'employee' && $user['role'] !== 'admin')
    );
    if (!$user || !$roleAllowed || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'success' => true,
        'message' => 'เข้าสู่ระบบสำเร็จ',
        'user' => [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'employee' => $user['employee_id'] === null ? null : [
                'id' => (int) $user['employee_id'],
                'employee_code' => $user['employee_code'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอ่านข้อมูลผู้ใช้'], JSON_UNESCAPED_UNICODE);
}
