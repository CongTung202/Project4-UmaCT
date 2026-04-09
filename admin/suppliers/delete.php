<?php
require_once '../../config/config.php';
require_once '../../models/supplier_model.php';

if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID để xóa.");
}

$id = $_GET['id'];

try {
    deleteSupplier($id);
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    // Bắt lỗi nếu nhà cung cấp này đang có sản phẩm trong bảng products
    die("<div style='margin:20px; font-family:sans-serif;'>
            <h2 style='color:red'>Lỗi Xóa: " . htmlspecialchars($e->getMessage()) . "</h2>
            <p>Giải pháp: Hãy vào phần Quản lý Sản phẩm, chuyển các sản phẩm của nhà cung cấp này sang nhà cung cấp khác trước khi xóa.</p>
            <a href='index.php'>Quay lại danh sách</a>
         </div>");
}
?>