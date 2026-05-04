<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// 1. Cập nhật thông tin User (Tự động điền cho lần sau)
$update_profile = [
    'full_name' => $data['full_name'],
    'address' => $data['address'],
    'phone' => $data['phone']
];

$ch = curl_init(API_URL . '/users/' . $data['user_id'] . '/profile-lite');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_profile));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

// Cập nhật lại session để lần sau vào trang web đã có sẵn tên
$_SESSION['user']['full_name'] = $data['full_name'];
$_SESSION['user']['address'] = $data['address'];
$_SESSION['user']['phone'] = $data['phone'];

// 2. Tạo đơn hàng
$order_data = [
    'user_id' => $data['user_id'],
    'total_price' => $data['total_price'],
    'shipping_address' => $data['address'],
    'payment_method' => (int)$data['payment_method'],
    'items' => $data['items']
];

$ch_order = curl_init(API_URL . '/orders');
curl_setopt($ch_order, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_order, CURLOPT_POST, true);
curl_setopt($ch_order, CURLOPT_POSTFIELDS, json_encode($order_data));
curl_setopt($ch_order, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch_order);
curl_close($ch_order);

echo $response;