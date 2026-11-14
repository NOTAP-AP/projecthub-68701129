<?php
require_once __DIR__ . '/condb.php';
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isMultipart = strpos($contentType, 'multipart/form-data') === 0;
$data = $isMultipart ? $_POST : json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'GET') {
        $role = $_SESSION['role'] ?? '';
        $canReview = in_array($role, ['admin', 'hr'], true);
        if (in_array($role, ['admin', 'hr', 'manager'], true)) {
            $stmt = $conn->query(
                'SELECT l.id, l.employee_id, e.employee_code, e.first_name, e.last_name,
                        l.leave_type, l.start_date, l.end_date, l.reason, l.status,
                        l.reviewed_by, l.reviewed_at, l.created_at,
                        EXISTS (SELECT 1 FROM leave_request_attachments a WHERE a.leave_request_id = l.id) AS has_attachment
                 FROM leave_requests l JOIN employees e ON e.id = l.employee_id
                 ORDER BY l.created_at DESC'
            );
        } else {
            $stmt = $conn->prepare(
                'SELECT l.id, l.employee_id, e.employee_code, e.first_name, e.last_name,
                        l.leave_type, l.start_date, l.end_date, l.reason, l.status,
                        l.reviewed_by, l.reviewed_at, l.created_at,
                        EXISTS (SELECT 1 FROM leave_request_attachments a WHERE a.leave_request_id = l.id) AS has_attachment
                 FROM leave_requests l JOIN employees e ON e.id = l.employee_id
                 JOIN users u ON u.employee_id = e.id
                 WHERE u.id = ? ORDER BY l.created_at DESC'
            );
            $stmt->execute([$_SESSION['user_id']]);
        }
        echo json_encode([
            'success' => true,
            'can_review' => $canReview,
            'data' => $stmt->fetchAll(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ข้อมูลคำขอลาไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $role = $_SESSION['role'] ?? '';
        $employeeId = null;
        if (in_array($role, ['admin', 'hr'], true) && isset($data['employee_id'])) {
            $employeeId = filter_var($data['employee_id'], FILTER_VALIDATE_INT);
        } else {
            $stmt = $conn->prepare('SELECT employee_id FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $employeeId = $stmt->fetchColumn();
        }
        $leaveType = $data['leave_type'] ?? '';
        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        $allowedTypes = ['Annual', 'Sick', 'Personal', 'Other'];
        $start = is_string($startDate) ? DateTime::createFromFormat('!Y-m-d', $startDate) : false;
        $end = is_string($endDate) ? DateTime::createFromFormat('!Y-m-d', $endDate) : false;
        $validStart = $start && $start->format('Y-m-d') === $startDate;
        $validEnd = $end && $end->format('Y-m-d') === $endDate;
        if (!$employeeId || !in_array($leaveType, $allowedTypes, true) || !$validStart || !$validEnd || $end < $start) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'ข้อมูลคำขอลาไม่ครบหรือไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $_FILES['attachment'];
            if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาลองอีกครั้ง'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $fileSize = filesize($upload['tmp_name']);
            if ($fileSize === false || $fileSize === 0 || $fileSize > 5 * 1024 * 1024) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'ไฟล์ต้องมีขนาดไม่เกิน 5 MB'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $allowedTypes = [
                'application/pdf' => ['pdf'],
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
            ];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($upload['tmp_name']);
            $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
            if (!isset($allowedTypes[$mimeType]) || !in_array($extension, $allowedTypes[$mimeType], true)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะไฟล์ PDF, JPG และ PNG'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $fileContents = file_get_contents($upload['tmp_name']);
            if ($fileContents === false) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'อ่านไฟล์แนบไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $originalName = basename(str_replace('\\', '/', $upload['name']));
            $originalName = preg_replace('/[\x00-\x1F\x7F]/', '', $originalName);
            if (!preg_match('/^.{1,255}/us', $originalName, $nameMatch)) {
                $originalName = 'attachment.' . $extension;
            } else {
                $originalName = $nameMatch[0];
            }
            $attachment = [
                'name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'contents' => $fileContents,
            ];
        }

        $conn->beginTransaction();
        $stmt = $conn->prepare(
            'INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$employeeId, $leaveType, $startDate, $endDate, $data['reason'] ?? null]);
        $requestId = (int) $conn->lastInsertId();

        if ($attachment !== null) {
            $attachmentStmt = $conn->prepare(
                'INSERT INTO leave_request_attachments
                    (leave_request_id, original_name, mime_type, file_size, file_data)
                 VALUES (?, ?, ?, ?, \'\')'
            );
            $attachmentStmt->bindValue(1, $requestId, PDO::PARAM_INT);
            $attachmentStmt->bindValue(2, $attachment['name'], PDO::PARAM_STR);
            $attachmentStmt->bindValue(3, $attachment['mime_type'], PDO::PARAM_STR);
            $attachmentStmt->bindValue(4, $attachment['file_size'], PDO::PARAM_INT);
            $attachmentStmt->execute();

            $chunkStmt = $conn->prepare(
                'INSERT INTO leave_request_attachment_chunks (leave_request_id, chunk_index, chunk_data)
                 VALUES (?, ?, ?)'
            );
            $chunkSize = 256 * 1024;
            $contentLength = strlen($attachment['contents']);
            for ($offset = 0, $chunkIndex = 0; $offset < $contentLength; $offset += $chunkSize, $chunkIndex++) {
                $chunk = substr($attachment['contents'], $offset, $chunkSize);
                $chunkStmt->bindValue(1, $requestId, PDO::PARAM_INT);
                $chunkStmt->bindValue(2, $chunkIndex, PDO::PARAM_INT);
                $chunkStmt->bindValue(3, $chunk, PDO::PARAM_LOB);
                $chunkStmt->execute();
            }
        }
        $conn->commit();

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'ส่งคำขอลาสำเร็จ', 'id' => $requestId], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'PUT') {
        if (!in_array($_SESSION['role'] ?? '', ['admin', 'hr'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'เฉพาะผู้ดูแลระบบหรือ HR ที่อนุมัติคำขอได้'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $status = is_array($data) ? ($data['status'] ?? '') : '';
        if (!$id || !in_array($status, ['Approved', 'Rejected'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'ระบุ id และ status เป็น Approved หรือ Rejected'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt = $conn->prepare(
            'UPDATE leave_requests SET status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $stmt->execute([$status, $_SESSION['user_id'], $id]);
        if (!$stmt->rowCount()) {
            $check = $conn->prepare('SELECT id FROM leave_requests WHERE id = ?');
            $check->execute([$id]);
            if (!$check->fetchColumn()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'ไม่พบคำขอลา'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        echo json_encode(['success' => true, 'message' => 'บันทึกผลการพิจารณาแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Allow: GET, POST, PUT, OPTIONS');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'ไม่รองรับ HTTP method นี้'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log($e->getMessage());
    if ($conn->inTransaction()) {
        try {
            $conn->rollBack();
        } catch (PDOException $rollbackError) {
            error_log('Leave request rollback failed: ' . $rollbackError->getMessage());
        }
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ประมวลผลข้อมูลคำขอลาไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
}