<?php
require_once '../../config/config.php';
require_once '../../models/category_model.php';

if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID để xóa.");
}

$id = $_GET['id'];

try {
    deleteCategory($id);
    // Nếu xóa thành công, nhảy về trang danh sách
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    // Nếu lỗi (ví dụ do khóa ngoại), in lỗi ra màn hình
    die("<h2 style='color:red'>Lỗi Xóa: " . htmlspecialchars($e->getMessage()) . "</h2><br><a href='index.php'>Quay lại</a>");
}
?>