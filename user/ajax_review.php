<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

// 1. Phải đăng nhập mới được bình luận
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để bình luận!']);
    exit;
}

// 2. Nhận dữ liệu từ form gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($data['product_id'] ?? 0);
$rating = (int)($data['rating'] ?? 5);
$comment = trim($data['comment'] ?? '');

if ($product_id <= 0 || empty($comment)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung đánh giá!']);
    exit;
}

// 3. Chuẩn bị gói dữ liệu gửi sang Python
$payload = [
    'user_id' => $_SESSION['user']['id'],
    'rating' => $rating,
    'comment' => $comment
];

// 4. Gọi API Python
$ch = curl_init(API_URL . '/products/' . $product_id . '/reviews');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo $response ?: json_encode(['status' => 'error', 'message' => 'Lỗi kết nối máy chủ!']);