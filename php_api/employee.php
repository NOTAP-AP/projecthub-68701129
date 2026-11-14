<?php
require_once __DIR__ . '/condb.php';

session_start();
if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
	exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$body = json_decode(file_get_contents('php://input'), true);

if (in_array($method, ['POST', 'PUT', 'DELETE'], true) &&
	!in_array($_SESSION['role'] ?? '', ['admin', 'hr'], true)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์แก้ไขข้อมูลพนักงาน'], JSON_UNESCAPED_UNICODE);
	exit;
}

function respond($status, $payload)
{
	http_response_code($status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

function employeePayload($conn, $id)
{
	$stmt = $conn->prepare(
		'SELECT e.id, e.employee_code, e.first_name, e.last_name, e.email, e.phone,
				e.department_id, d.name AS department, e.position_id, p.name AS position,
				e.hire_date, e.status, e.created_at, e.updated_at
		 FROM employees e
		 LEFT JOIN departments d ON d.id = e.department_id
		 LEFT JOIN positions p ON p.id = e.position_id
		 WHERE e.id = ?'
	);
	$stmt->execute([$id]);
	return $stmt->fetch();
}

function validateEmployee($data)
{
	$required = ['employee_code', 'first_name', 'last_name'];
	foreach ($required as $field) {
		if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
			respond(422, ['success' => false, 'message' => "กรุณาระบุ {$field}"]);
		}
	}

	if (isset($data['email']) && $data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
		respond(422, ['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง']);
	}

	$allowedStatuses = ['Active', 'Resigned', 'Suspended'];
	if (isset($data['status']) && !in_array($data['status'], $allowedStatuses, true)) {
		respond(422, ['success' => false, 'message' => 'สถานะพนักงานไม่ถูกต้อง']);
	}

	foreach (['department_id', 'position_id'] as $field) {
		if (isset($data[$field]) && $data[$field] !== '' && filter_var($data[$field], FILTER_VALIDATE_INT) === false) {
			respond(422, ['success' => false, 'message' => "{$field} ต้องเป็นตัวเลข"]);
		}
	}

	if (isset($data['hire_date']) && $data['hire_date'] !== '') {
		$date = DateTime::createFromFormat('Y-m-d', $data['hire_date']);
		if (!$date || $date->format('Y-m-d') !== $data['hire_date']) {
			respond(422, ['success' => false, 'message' => 'วันที่เริ่มงานต้องอยู่ในรูปแบบ YYYY-MM-DD']);
		}
	}
}

try {
	if ($method === 'GET') {
		if ($id !== false && $id !== null) {
			$employee = employeePayload($conn, $id);
			if (!$employee) {
				respond(404, ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน']);
			}
			respond(200, ['success' => true, 'data' => $employee]);
		}

		$stmt = $conn->query(
			'SELECT e.id, e.employee_code, e.first_name, e.last_name, e.email, e.phone,
					e.department_id, d.name AS department, e.position_id, p.name AS position,
					e.hire_date, e.status, e.created_at, e.updated_at
			 FROM employees e
			 LEFT JOIN departments d ON d.id = e.department_id
			 LEFT JOIN positions p ON p.id = e.position_id
			 ORDER BY e.id DESC'
		);
		respond(200, ['success' => true, 'data' => $stmt->fetchAll()]);
	}

	if ($method === 'POST') {
		if (!is_array($body)) {
			respond(400, ['success' => false, 'message' => 'ต้องส่งข้อมูล JSON']);
		}
		validateEmployee($body);

		$stmt = $conn->prepare(
			'INSERT INTO employees
			 (employee_code, first_name, last_name, email, phone, department_id, position_id, hire_date, status)
			 VALUES (:employee_code, :first_name, :last_name, :email, :phone, :department_id, :position_id, :hire_date, :status)'
		);
		$stmt->execute([
			'employee_code' => trim($body['employee_code']),
			'first_name' => trim($body['first_name']),
			'last_name' => trim($body['last_name']),
			'email' => !empty($body['email']) ? trim($body['email']) : null,
			'phone' => !empty($body['phone']) ? trim($body['phone']) : null,
			'department_id' => !empty($body['department_id']) ? (int) $body['department_id'] : null,
			'position_id' => !empty($body['position_id']) ? (int) $body['position_id'] : null,
			'hire_date' => !empty($body['hire_date']) ? $body['hire_date'] : null,
			'status' => $body['status'] ?? 'Active',
		]);

		$employee = employeePayload($conn, (int) $conn->lastInsertId());
		respond(201, ['success' => true, 'message' => 'เพิ่มข้อมูลพนักงานสำเร็จ', 'data' => $employee]);
	}

	if ($method === 'PUT') {
		if (!$id || !is_array($body)) {
			respond(400, ['success' => false, 'message' => 'ต้องระบุ id และส่งข้อมูล JSON']);
		}
		$current = employeePayload($conn, $id);
		if (!$current) {
			respond(404, ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน']);
		}
		$updated = array_merge($current, $body);
		validateEmployee($updated);

		$stmt = $conn->prepare(
			'UPDATE employees SET employee_code = :employee_code, first_name = :first_name,
			 last_name = :last_name, email = :email, phone = :phone, department_id = :department_id,
			 position_id = :position_id, hire_date = :hire_date, status = :status WHERE id = :id'
		);
		$stmt->execute([
			'employee_code' => trim($updated['employee_code']),
			'first_name' => trim($updated['first_name']),
			'last_name' => trim($updated['last_name']),
			'email' => !empty($updated['email']) ? trim($updated['email']) : null,
			'phone' => !empty($updated['phone']) ? trim($updated['phone']) : null,
			'department_id' => !empty($updated['department_id']) ? (int) $updated['department_id'] : null,
			'position_id' => !empty($updated['position_id']) ? (int) $updated['position_id'] : null,
			'hire_date' => !empty($updated['hire_date']) ? $updated['hire_date'] : null,
			'status' => $updated['status'],
			'id' => $id,
		]);

		respond(200, ['success' => true, 'message' => 'แก้ไขข้อมูลพนักงานสำเร็จ', 'data' => employeePayload($conn, $id)]);
	}

	if ($method === 'DELETE') {
		if (!$id) {
			respond(400, ['success' => false, 'message' => 'ต้องระบุ id พนักงาน']);
		}
		$stmt = $conn->prepare('DELETE FROM employees WHERE id = ?');
		$stmt->execute([$id]);
		if (!$stmt->rowCount()) {
			respond(404, ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน']);
		}
		respond(200, ['success' => true, 'message' => 'ลบข้อมูลพนักงานสำเร็จ']);
	}

	header('Allow: GET, POST, PUT, DELETE, OPTIONS');
	respond(405, ['success' => false, 'message' => 'ไม่รองรับ HTTP method นี้']);
} catch (PDOException $e) {
	if ($e->getCode() === '23000') {
		respond(409, ['success' => false, 'message' => 'รหัสพนักงานหรืออีเมลนี้มีอยู่ในระบบแล้ว']);
	}
	error_log($e->getMessage());
	respond(500, ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการประมวลผลข้อมูลพนักงาน']);
}
