<?php
require_once '../../config/config.php';
require_once '../../models/article_model.php';

if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID để xóa.");
}

$id = $_GET['id'];

try {
    deleteArticle($id);
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    die("<h2 style='color:red; margin:20px;'>Lỗi Xóa: " . htmlspecialchars($e->getMessage()) . "</h2><a href='index.php'>Quay lại</a>");
}
?>