<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

// Kiểm tra quyền Admin (1) hoặc Staff (3)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role_id'], [1, 3])) {
    echo json_encode(['status' => 'error', 'message' => 'Bác không có quyền thực hiện thao tác này!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$review_id = (int)($data['review_id'] ?? 0);
$staff_reply = trim($data['staff_reply'] ?? '');

if ($review_id <= 0 || empty($staff_reply)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung trả lời!']);
    exit;
}

// Gọi API Python
$payload = ['staff_reply' => $staff_reply];
$ch = curl_init(API_URL . '/reviews/' . $review_id . '/reply');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo $response ?: json_encode(['status' => 'error', 'message' => 'Lỗi kết nối máy chủ!']);