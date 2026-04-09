<?php
require_once '../../config/config.php';
require_once '../../models/voucher_model.php';

if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID mã giảm giá để xóa.");
}

$id = $_GET['id'];

try {
    deleteVoucher($id);
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    // Nếu mã này đã được lưu trong bảng user_voucher_usage hoặc orders, hệ thống sẽ chặn xóa
    die("<div style='margin:20px; font-family:sans-serif;'>
            <h2 style='color:red'>Lỗi Xóa: " . htmlspecialchars($e->getMessage()) . "</h2>
            <p>Giải pháp: Không thể xóa mã đã được khách sử dụng. Hãy vào phần Sửa và chỉnh <b>Hạn sử dụng</b> về ngày hôm qua để vô hiệu hóa mã.</p>
            <a href='index.php'>Quay lại danh sách</a>
         </div>");
}
?>