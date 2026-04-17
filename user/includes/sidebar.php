<?php
// Gọi model danh mục (Đường dẫn lùi 2 cấp từ thư mục includes)
require_once __DIR__ . '/../../models/category_model.php';
$categories = getAllCategories();
?>

<aside class="sidebar">
    <div class="sidebar-section">
        <div class="sidebar-title">Menu Chính</div>
        <a href="<?= BASE_URL ?>/user/index.php" class="sidebar-item <?= (strpos($current_url, '/user/index.php') !== false || $current_url == '/user/') ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Trang chủ
        </a>
        <a href="<?= BASE_URL ?>/user/products.php" class="sidebar-item <?= (strpos($current_url, '/products.php') !== false) ? 'active' : '' ?>">
            <i class="fas fa-box-open"></i> Tất cả sản phẩm
        </a>
        <a href="<?= BASE_URL ?>/user/about.php" class="sidebar-item <?= (strpos($current_url, '/about.php') !== false) ? 'active' : '' ?>">
            <i class="fas fa-info-circle"></i> Về chúng tôi
        </a>
        <a href="<?= BASE_URL ?>/user/contact.php" class="sidebar-item <?= (strpos($current_url, '/contact.php') !== false) ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Liên hệ
        </a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-title">Danh Mục</div>
        <?php foreach($categories as $cat): ?>
            <a href="<?= BASE_URL ?>/user/products.php?category=<?= $cat['id'] ?>" class="sidebar-item">
                <i class="fas fa-angle-right"></i> <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</aside>