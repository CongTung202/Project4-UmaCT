<aside class="sidebar">
    <div class="sidebar-header">
        <h2>UmaCT Admin</h2>
    </div>
    
    <ul class="nav-links">
        <li>
            <a href="<?= BASE_URL ?>/admin/index.php" class="<?= (strpos($current_url, '/admin/index.php') !== false) ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Tổng quan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/categories/index.php" class="<?= (strpos($current_url, '/categories/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i> Danh mục
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/suppliers/index.php" class="<?= (strpos($current_url, '/suppliers/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-building"></i> Nhà cung cấp
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/products/index.php" class="<?= (strpos($current_url, '/products/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-box-open"></i> Sản phẩm
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/orders/index.php" class="<?= (strpos($current_url, '/orders/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Đơn hàng
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/vouchers/index.php" class="<?= (strpos($current_url, '/vouchers/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-ticket-alt"></i> Mã giảm giá
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="<?= (strpos($current_url, '/users/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Người dùng
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/articles/index.php" class="<?= (strpos($current_url, '/articles/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i> Quản lý Bài viết
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/banners/index.php" class="<?= (strpos($current_url, '/banners/') !== false) ? 'active' : '' ?>">
                <i class="fas fa-images"></i> Quản lý Banner
            </a>
        </li>
    </ul>

    <div style="margin-top: auto; padding: 20px;">
        <a href="<?= BASE_URL ?>/user/index.php" style="display: flex; align-items: center; justify-content: center; background: #fff3f3; color: #ff3333; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: bold; border: 1px solid #ffb3b3; transition: 0.3s;" onmouseover="this.style.background='#ff3333'; this.style.color='#fff';" onmouseout="this.style.background='#fff3f3'; this.style.color='#ff3333';">
            <i class="fas fa-store" style="margin-right: 8px;"></i> Về trang mua sắm
        </a>
    </div>
</aside>