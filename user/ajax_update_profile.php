<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập lại!']);
    exit;
}

// Nhận dữ liệu từ form
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user']['id'];

// Chuẩn bị dữ liệu gửi sang Python API (Giống hệt cấu trúc ở Checkout)
$update_profile = [
    'full_name' => trim($data['full_name'] ?? ''),
    'address'   => trim($data['address'] ?? ''),
    'phone'     => trim($data['phone'] ?? '')
];

if (empty($update_profile['full_name']) || empty($update_profile['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đủ Họ tên và Số điện thoại!']);
    exit;
}

// Gọi API cập nhật
$ch = curl_init(API_URL . '/users/' . $user_id . '/profile-lite');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_profile));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    // Cập nhật lại Session để giao diện hiển thị ngay lập tức
    $_SESSION['user']['full_name'] = $update_profile['full_name'];
    $_SESSION['user']['address']   = $update_profile['address'];
    $_SESSION['user']['phone']     = $update_profile['phone'];
    
    echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật thông tin thành công!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra từ máy chủ, vui lòng thử lại!']);
}