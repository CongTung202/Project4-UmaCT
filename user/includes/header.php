<?php
// Gọi file config chung của dự án
require_once __DIR__ . '/../../config/config.php';
$current_url = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UmaCT - Cửa hàng Mô hình Anime</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core-layout.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="<?= BASE_URL ?>/user/index.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="UmaCT Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/100x22?text=UmaCT'">
            <span style="font-weight: bold; font-size: 20px; color: #ff3333;">UmaCT</span>
        </a>
    </div>
    
    <div class="header-actions" style="display: flex; gap: 20px; align-items: center;">
        <div class="search-bar" style="position: relative;">
            <input type="text" class="form-input" placeholder="Tìm kiếm sản phẩm..." style="width: 300px; border-radius: 20px; padding: 8px 15px;">
            <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        </div>
        <a href="<?= BASE_URL ?>/user/cart.php" style="color: #333; font-size: 20px; position: relative;"><i class="fas fa-shopping-cart"></i><span style="position: absolute; top: -8px; right: -10px; background: #ff3333; color: white; font-size: 10px; padding: 2px 6px; border-radius: 50%;">0</span></a>
        <a href="<?= BASE_URL ?>/user/login.php" style="color: #333; font-size: 20px;"><i class="fas fa-user-circle"></i></a>
    </div>
</header>

<?php require_once 'sidebar.php'; ?>

<?php require_once 'right_sidebar.php'; ?>

<main class="main-content">