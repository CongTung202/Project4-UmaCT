<?php
// Bắt đầu session để nhận diện phiên làm việc hiện tại
session_start();

// Xóa toàn bộ dữ liệu (bao gồm cả $_SESSION['user'], $_SESSION['cart']...)
session_unset();

// Hủy hoàn toàn phiên làm việc (Session) này
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập (hoặc trang chủ)
header("Location: login.php");
exit;
?>