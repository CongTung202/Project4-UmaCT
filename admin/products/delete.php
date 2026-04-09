<?php
require_once '../../config/config.php';
require_once '../../models/product_model.php';

if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID sản phẩm để xóa.");
}

$id = $_GET['id'];

try {
    deleteProduct($id);
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    // Nếu bị vướng khóa ngoại (sản phẩm đã có người mua)
    die("<div style='margin:20px; font-family:sans-serif;'>
            <h2 style='color:red'>Lỗi Xóa: " . htmlspecialchars($e->getMessage()) . "</h2>
            <p>Giải pháp: Thay vì xóa, bạn nên vào phần Sửa và đổi trạng thái Sản phẩm thành 'Ngừng bán'.</p>
            <a href='index.php'>Quay lại danh sách</a>
         </div>");
}
?>