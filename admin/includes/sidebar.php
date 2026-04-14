<aside class="sidebar">
    <div class="sidebar-header">
        <h2>UmaCT Admin</h2>
    </div>
    <ul class="nav-links">
        <li>
            <a href="<?= BASE_URL ?>/admin/index.php" class="<?= (strpos($current_url, '/admin/index.php') !== false) ? 'active' : '' ?>">
                Tổng quan
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/categories/index.php" class="<?= (strpos($current_url, '/categories/') !== false) ? 'active' : '' ?>">
                Danh mục
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/suppliers/index.php" class="<?= (strpos($current_url, '/suppliers/') !== false) ? 'active' : '' ?>">
                Nhà cung cấp
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/products/index.php" class="<?= (strpos($current_url, '/products/') !== false) ? 'active' : '' ?>">
                Sản phẩm
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/orders/index.php" class="<?= (strpos($current_url, '/orders/') !== false) ? 'active' : '' ?>">
                Đơn hàng
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/vouchers/index.php" class="<?= (strpos($current_url, '/vouchers/') !== false) ? 'active' : '' ?>">
                Mã giảm giá
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="<?= (strpos($current_url, '/users/') !== false) ? 'active' : '' ?>">
                Người dùng
            </a>
        </li>
        
        <li>
            <a href="<?= BASE_URL ?>/admin/articles/index.php" class="<?= (strpos($current_url, '/articles/') !== false) ? 'active' : '' ?>">
                Quản lý Bài viết
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/banners/index.php" class="<?= (strpos($current_url, '/banners/') !== false) ? 'active' : '' ?>">
                Quản lý Banner
            </a>
        </li>
    </ul>
</aside>