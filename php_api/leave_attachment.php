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
    header('Allow: GET');
    echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะ GET'], JSON_UNESCAPED_UNICODE);
    exit;
}

$requestId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$requestId || $requestId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'รหัสไฟล์ไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $conn->prepare(
        'SELECT a.leave_request_id, a.original_name, a.mime_type, a.file_size, a.file_data
         FROM leave_request_attachments a
         JOIN leave_requests l ON l.id = a.leave_request_id
         WHERE a.leave_request_id = ?
           AND (? IN (\'admin\', \'hr\', \'manager\')
                OR l.employee_id = (SELECT employee_id FROM users WHERE id = ?))
         LIMIT 1'
    );
    $stmt->execute([
        $requestId,
        $_SESSION['role'] ?? '',
        $_SESSION['user_id'],
    ]);
    $attachment = $stmt->fetch();

    if (!$attachment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'ไม่พบไฟล์แนบ'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fileData = $attachment['file_data'];
    if (is_resource($fileData)) {
        $fileData = stream_get_contents($fileData);
    }

    if (!is_string($fileData)) {
        throw new RuntimeException('Stored attachment data is invalid');
    }

    if ($fileData === '') {
        $chunkStmt = $conn->prepare(
            'SELECT chunk_data
             FROM leave_request_attachment_chunks
             WHERE leave_request_id = ?
             ORDER BY chunk_index'
        );
        $chunkStmt->execute([$attachment['leave_request_id']]);
        $chunks = [];
        $totalBytes = 0;
        while (($chunk = $chunkStmt->fetchColumn()) !== false) {
            if (is_resource($chunk)) {
                $chunk = stream_get_contents($chunk);
            }
            if (!is_string($chunk)) {
                throw new RuntimeException('Stored attachment chunk is invalid');
            }
            $chunks[] = $chunk;
            $totalBytes += strlen($chunk);
        }

        if ($totalBytes !== (int) $attachment['file_size']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'ไฟล์แนบไม่สมบูรณ์'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $fileData = implode('', $chunks);
    }

    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $mimeType = in_array($attachment['mime_type'], $allowedMimeTypes, true)
        ? $attachment['mime_type']
        : 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="attachment"; filename*=UTF-8\'\'' . rawurlencode($attachment['original_name']));
    header('Content-Length: ' . strlen($fileData));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');

    echo $fileData;
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ดาวน์โหลดไฟล์ไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ดาวน์โหลดไฟล์ไม่สำเร็จ'], JSON_UNESCAPED_UNICODE);
}
