<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập lại!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user']['id'];

// Gọi cURL sang Python
$ch = curl_init(API_URL . '/users/' . $user_id . '/password');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'current_password' => $data['current_password'] ?? '',
    'new_password' => $data['new_password'] ?? ''
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>