<?php 
require_once __DIR__ . '/../../config/config.php'; 

// Lấy đường dẫn URL hiện tại để Sidebar biết đang đứng ở tab nào
$current_url = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>UmaCT - Bảng Điều Khiển</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
    
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">