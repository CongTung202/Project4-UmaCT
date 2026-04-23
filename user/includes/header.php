<?php
// Bắt đầu session nếu chưa có (để lấy dữ liệu xem người dùng đã đăng nhập chưa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    
    <style>
        /* CSS cho cấu trúc Header 3 phần */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            height: 60px;
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            position: fixed;
            top: 0; left: 0; width: 100%;
            z-index: 1000;
        }
        
        .header-left { flex: 1; display: flex; align-items: center; }
        .header-center { flex: 2; display: flex; justify-content: center; }
        .header-right { flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 20px; }

        /* Form Tìm Kiếm */
        .search-form {
            position: relative;
            width: 100%;
            max-width: 500px;
        }
        .search-form .form-input {
            width: 100%;
            border-radius: 30px;
            padding: 10px 45px 10px 20px;
            border: 1px solid #ddd;
            outline: none;
            transition: border-color 0.3s;
        }
        .search-form .form-input:focus { border-color: #ff3333; }
        .search-form button {
            position: absolute; right: 5px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #666; cursor: pointer;
            padding: 8px; border-radius: 50%; transition: color 0.3s;
        }
        .search-form button:hover { color: #ff3333; }

        /* Các Icon */
        .header-icon {
            color: #333; font-size: 20px; position: relative; text-decoration: none; transition: color 0.3s;
        }
        .header-icon:hover { color: #ff3333; }
        .badge {
            position: absolute; top: -6px; right: -8px;
            background: #ff3333; color: white; font-size: 10px;
            padding: 2px 5px; border-radius: 10px; font-weight: bold;
        }
        
        /* Nút Đăng nhập nổi bật */
        .btn-login-header {
            background: #ff3333; color: #fff; padding: 8px 20px; 
            border-radius: 20px; text-decoration: none; font-size: 14px; 
            font-weight: bold; transition: background 0.3s; margin-left: 10px;
        }
        .btn-login-header:hover { background: #e60000; color: #fff; }
        .search-suggestions {
            position: absolute; top: 100%; left: 0; width: 100%;
            background: #fff; border: 1px solid #ddd; border-top: none;
            border-radius: 0 0 8px 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            z-index: 1001; overflow: hidden;
        }
        .suggest-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 15px;
            text-decoration: none; color: #333; border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        .suggest-item:hover { background: #f9f9f9; }
        .suggest-item img { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
        .suggest-info { display: flex; flex-direction: column; }
        .suggest-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 350px;}
        .suggest-price { font-size: 13px; color: #ff3333; font-weight: bold; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="header-left">
        <a href="<?= BASE_URL ?>/user/index.php" style="text-decoration: none;">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="UmaCT Logo" class="logo-img" style="height: 25px;" onerror="this.onerror=null; this.src='https://via.placeholder.com/100x25?text=UmaCT'">
        </a>
    </div>
    
    <div class="header-center">
        <form action="<?= BASE_URL ?>/user/products.php" method="GET" class="search-form" style="position: relative;">
            <input type="text" name="keyword" id="searchInput" class="form-input" placeholder="Tìm kiếm mô hình, waifu..." autocomplete="off" value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
            <button type="submit" title="Tìm kiếm"><i class="fas fa-search"></i></button>
            
            <div id="searchSuggestions" class="search-suggestions" style="display: none;">
                </div>
        </form>
    </div>

    <div class="header-right">
        <a href="#" class="header-icon" title="Voucher">
            <i class="fas fa-ticket-alt"></i>
        </a>
        
        <a href="#" class="header-icon" title="Yêu thích">
            <i class="fas fa-heart"></i>
            <span class="badge">0</span>
        </a>

        <?php $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
        
        <a href="<?= BASE_URL ?>/user/cart.php" class="header-icon" title="Giỏ hàng">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge" id="cart-badge" style="display: inline-block;"><?= $cart_count ?></span>
        </a>

        <?php if(isset($_SESSION['user'])): ?>
            <a href="<?= BASE_URL ?>/user/profile.php" style="color: #333; text-decoration: none; display: flex; align-items: center; gap: 8px; margin-left: 15px;">
                <i class="fas fa-user-circle" style="font-size: 24px; color: #ff3333;"></i>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/user/login.php" class="btn-login-header">Đăng nhập</a>
        <?php endif; ?>
    </div>
</header>
<script>
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        let timeoutId;

        // Bắt sự kiện khi người dùng gõ phím
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const keyword = this.value.trim();

            if (keyword.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }

            // Đợi 300ms sau khi ngừng gõ mới gọi API để đỡ lag
            timeoutId = setTimeout(() => {
                fetch(`<?= BASE_URL ?>/user/ajax_search.php?q=${encodeURIComponent(keyword)}`)
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success' && res.data.length > 0) {
                            let html = '';
                            res.data.forEach(p => {
                                const img = p.main_image ? p.main_image : 'https://via.placeholder.com/40';
                                // Định dạng tiền tệ
                                const price = new Intl.NumberFormat('vi-VN').format(p.price) + ' đ';
                                
                                html += `
                                    <a href="<?= BASE_URL ?>/user/detail.php?id=${p.id}" class="suggest-item">
                                        <img src="${img}" alt="img">
                                        <div class="suggest-info">
                                            <span class="suggest-name">${p.name}</span>
                                            <span class="suggest-price">${price}</span>
                                        </div>
                                    </a>
                                `;
                            });
                            searchSuggestions.innerHTML = html;
                            searchSuggestions.style.display = 'block';
                        } else {
                            searchSuggestions.innerHTML = '<div style="padding: 15px; text-align: center; color: #888; font-size: 13px;">Không tìm thấy sản phẩm</div>';
                            searchSuggestions.style.display = 'block';
                        }
                    });
            }, 300);
        });

        // Tắt khung gợi ý khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    </script>
<?php require_once 'sidebar.php'; ?>

<?php 
// 1. Khai báo các trang KHÔNG muốn hiện cột Tin tức bên phải
$hide_right_sidebar_pages = ['cart.php', 'checkout.php'];
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Chỉ gọi file right_sidebar.php nếu trang hiện tại KHÔNG nằm trong danh sách trên
if (!in_array($current_page, $hide_right_sidebar_pages)) {
    require_once 'right_sidebar.php'; 
}
?>

<main class="main-content <?= in_array($current_page, $hide_right_sidebar_pages) ? 'expanded-mode' : '' ?>">