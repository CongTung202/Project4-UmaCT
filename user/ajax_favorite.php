<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích!']);
    exit;
}

// 2. Nhận dữ liệu từ Javascript gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$user_id = $_SESSION['user']['id'];

if ($product_id > 0) {
    // 3. Đóng gói và gửi sang Python API
    $api_data = json_encode(['user_id' => $user_id, 'product_id' => $product_id]);
    
    $ch = curl_init(API_URL . '/favorites/toggle');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    // 4. Trả kết quả ngược lại cho Javascript
    echo $response;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
}