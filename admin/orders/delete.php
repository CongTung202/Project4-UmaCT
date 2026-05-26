<?php
session_start();
require_once '../../config/config.php'; // Đảm bảo file cấu hình được gọi để có biến API_URL

// 1. Kiểm tra ID truyền vào
if (!isset($_GET['id'])) {
    header("Location: index.php?error=" . urlencode("Lỗi: Không tìm thấy ID đơn hàng cần xóa."));
    exit;
}

$order_id = (int)$_GET['id'];

// 2. Gọi API Xóa bên Python bằng cURL
$ch = curl_init(API_URL . '/orders/' . $order_id);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 3. Phân tích kết quả và điều hướng về trang danh sách
$result = json_decode($response, true);

if ($http_code == 200 && isset($result['status']) && $result['status'] == 'success') {
    // Xóa thành công
    header("Location: index.php?success=" . urlencode("Đã dọn dẹp thành công đơn hàng #$order_id!"));
} else {
    // Lỗi (ví dụ: Không tìm thấy đơn hàng)
    $error_msg = $result['detail'] ?? "Lỗi không xác định từ máy chủ khi xóa đơn hàng.";
    header("Location: index.php?error=" . urlencode($error_msg));
}
exit;
?>