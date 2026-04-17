<?php 
require_once 'includes/header.php'; 
require_once '../models/banner_model.php';
require_once '../models/product_model.php';

// Nhúng CSS chuyên biệt cho trang chủ
echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/home.css">';

// 1. Lấy và phân loại Banner
$banners = getAllBanners();
$top_banners = array_filter($banners, fn($b) => $b['position'] == 'Trang chủ - Slider Top');
$side_banners = array_filter($banners, fn($b) => $b['position'] == 'Trang chủ - Cột bên');

// 2. Lấy Sản phẩm mới nhất (giới hạn 8 cái cho đẹp lưới)
$products = array_slice(array_filter(getAllProducts(), fn($p) => $p['is_active'] == 1), 0, 8);
?>

<div class="home-slider">
    <div class="slides-track">
        <?php foreach($top_banners as $b): ?>
            <a href="<?= $b['link'] ?>" class="slide"><img src="<?= $b['image_url'] ?>"></a>
        <?php endforeach; ?>
        <?php if(!empty($top_banners)): ?>
            <a href="<?= $top_banners[0]['link'] ?>" class="slide"><img src="<?= $top_banners[0]['image_url'] ?>"></a>
        <?php endif; ?>
    </div>
</div>

<div class="promo-container">
    <?php foreach(array_slice($side_banners, 0, 3) as $sb): ?>
        <a href="<?= $sb['link'] ?>" class="promo-card">
            <img src="<?= $sb['image_url'] ?>" alt="Promo">
        </a>
    <?php endforeach; ?>
</div>

<section class="new-arrivals">
    <div class="home-section-title">
        <h2>🔥 Sản phẩm mới cập bến</h2>
        <a href="products.php" class="btn-view-all">Xem tất cả <i class="fas fa-chevron-right"></i></a>
    </div>

    <div class="product-grid">
        <?php foreach($products as $p): ?>
            <div class="product-item">
                <a href="detail.php?id=<?= $p['id'] ?>" class="img-wrapper">
                    <img src="<?= $p['main_image'] ?? 'https://via.placeholder.com/240x240?text=No+Image' ?>" alt="<?= $p['name'] ?>">
                </a>
                <div class="info">
                    <a href="detail.php?id=<?= $p['id'] ?>" style="text-decoration: none;">
                        <div class="name"><?= htmlspecialchars($p['name']) ?></div>
                    </a>
                    <div class="price"><?= number_format($p['price'], 0, ',', '.') ?> đ</div>
                    <?php if($p['stock_quantity'] > 0): ?>
                        <div class="stock in"><i class="fas fa-check"></i> Còn hàng (<?= $p['stock_quantity'] ?>)</div>
                    <?php else: ?>
                        <div class="stock out"><i class="fas fa-times"></i> Tạm hết hàng</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>