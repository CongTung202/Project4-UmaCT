<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực!']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$payload = [
    'code' => trim($data['code'] ?? ''),
    'user_id' => $_SESSION['user']['id'],
    'cart_total' => (float)($data['cart_total'] ?? 0)
];

$ch = curl_init(API_URL . '/vouchers/validate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo $response ?: json_encode(['status' => 'error', 'message' => 'Lỗi kết nối máy chủ!']);