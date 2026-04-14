<?php
require_once '../../config/config.php';
require_once '../../models/banner_model.php';

if (!isset($_GET['id'])) {
    die("Thiếu ID banner cần xóa.");
}

$id = $_GET['id'];

try {
    deleteBanner($id);
    // Xóa xong thì quay về trang chủ banners
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    echo "<div style='padding: 20px; font-family: sans-serif;'>";
    echo "<h2 style='color: red;'>Lỗi khi xóa: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<a href='index.php' style='text-decoration: none; color: blue;'>Quay lại danh sách</a>";
    echo "</div>";
}