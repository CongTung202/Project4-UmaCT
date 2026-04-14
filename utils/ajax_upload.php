<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/upload_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    try {
        // Tận dụng lại hàm uploadImageToAPI đã viết ở bài trước
        $url = uploadImageToAPI($_FILES['image']);
        echo json_encode(['status' => 'success', 'url' => $url]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy file']);
}
?>