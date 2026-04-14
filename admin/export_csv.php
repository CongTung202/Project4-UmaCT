<?php
// Không include header hay footer vì chúng ta đang xuất file, không xuất HTML
require_once '../config/config.php';

// Tạm thời gọi trực tiếp API orders để lấy toàn bộ dữ liệu đơn hàng
$ch = curl_init(API_URL . '/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$orders = $result['data'] ?? [];

// Khai báo Header để trình duyệt hiểu đây là file tải về
$filename = "bao_cao_doanh_thu_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Mở luồng ghi trực tiếp ra trình duyệt
$output = fopen('php://output', 'w');

// Fix lỗi font tiếng Việt khi mở bằng Excel trên Windows
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Viết dòng tiêu đề các cột
fputcsv($output, ['Mã Đơn', 'Khách hàng', 'Trạng thái', 'Ngày đặt', 'Tổng tiền (VND)']);

// Lặp qua mảng đơn hàng và viết từng dòng vào file CSV
foreach ($orders as $order) {
    fputcsv($output, [
        $order['id'],
        $order['full_name'] ?? $order['username'],
        $order['status'],
        date('d/m/Y H:i', strtotime($order['created_at'])),
        $order['total_price']
    ]);
}

// Đóng luồng
fclose($output);
exit;
?>