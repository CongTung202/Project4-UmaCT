<?php
session_start();
require_once '../config/config.php';
require_once '../models/cart_model.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

if ($product_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// LOGIC: NẾU ĐÃ ĐĂNG NHẬP -> LƯU VÀO DB
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $result = addToCartDB($user_id, $product_id, $quantity);
    
    // Lấy lại tổng số lượng từ DB để trả về Header
    $cart_db = getCartFromDB($user_id);
    $total_items = 0;
    foreach($cart_db as $item) $total_items += $item['quantity'];
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã lưu vào giỏ hàng của bạn!',
        'total_items' => $total_items
    ]);
} else {
    // NẾU CHƯA ĐĂNG NHẬP -> VẪN DÙNG SESSION TẠM THỜI
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã thêm vào giỏ (Khách)!',
        'total_items' => array_sum($_SESSION['cart'])
    ]);
}