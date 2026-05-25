<?php
// 1. Gọi model và lấy bài viết, sản phẩm
require_once __DIR__ . '/../../models/article_model.php';
require_once __DIR__ . '/../../models/product_model.php';
$articles = getAllArticles();
$recent_articles = array_slice($articles, 0, 3); // Cắt lấy 3 bài viết mới nhất

// Hàm tự động lấy ảnh đầu tiên trong bài viết để làm thumbnail
if (!function_exists('getFirstImage')) {
    function getFirstImage($html) {
        if (preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $html, $matches)) {
            return $matches[1];
        }
        return BASE_URL . '/assets/images/2.png';
    }
}

if (!function_exists('getProductFirstImage')) {
    function getProductFirstImage($product) {
        // Trường hợp 1: Cột images chứa chuỗi JSON (Do API Python dùng json.dumps)
        if (!empty($product['images']) && is_string($product['images'])) {
            $img_array = json_decode($product['images'], true);
            // Lúc này $img_array sẽ có dạng ["link1.jpg", "link2.jpg"]
            if (is_array($img_array) && count($img_array) > 0) {
                return $img_array[0]; 
            }
        }
        
        // Trường hợp 2: Có sẵn cột main_image từ câu lệnh SQL (Nếu dùng API get_products chung)
        if (!empty($product['main_image'])) return $product['main_image'];
        if (!empty($product['image_url'])) return $product['image_url'];
        
        // Mặc định nếu hoàn toàn trống
        return 'https://placehold.co/100x100/eeeeee/666666?text=UmaCT';
    }
}

// Lấy danh sách ID từ cookie
$viewed_ids = isset($_COOKIE['recently_viewed']) ? json_decode($_COOKIE['recently_viewed'], true) : [];

// Nếu đang ở trang chi tiết và có ID sản phẩm, chèn vào đầu danh sách hiển thị
$current_product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if ($current_product_id) {
    if (($key = array_search($current_product_id, $viewed_ids)) !== false) {
        unset($viewed_ids[$key]);
    }
    array_unshift($viewed_ids, $current_product_id);
    $viewed_ids = array_slice($viewed_ids, 0, 5); // Giới hạn 5 sp
}

// Lấy thông tin sản phẩm từ các ID
$recent_products = [];
if (!empty($viewed_ids) && function_exists('getProductById')) {
    foreach ($viewed_ids as $vid) {
        $p = getProductById($vid);
        if ($p) {
            $recent_products[] = $p;
        }
    }
}
?>

<style>
    /* KHUNG SIDEBAR CHUẨN BÁO CHÍ */
    .news-sidebar {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        font-family: Arial, sans-serif;
    }

    .news-sidebar-title {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #ff3333;
        padding-bottom: 10px;
        margin-top: 0;
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    /* TỪNG ITEM (Sản phẩm / Bài viết) */
    .news-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
        text-decoration: none;
        transition: 0.2s;
    }
    .news-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    /* ẢNH VUÔNG BÊN TRÁI */
    .news-img-wrapper {
        width: 80px;
        height: 80px;
        flex-shrink: 0;
        position: relative;
    }
    .news-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #f0f0f0;
    }

    /* NỘI DUNG BÊN PHẢI */
    .news-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .news-name {
        font-size: 14px;
        color: #222;
        line-height: 1.4;
        margin: 0 0 5px 0;
        font-weight: 600;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .news-item:hover .news-name {
        color: #ff3333;
    }

    /* DÒNG META (Logo, Giá, Thời gian) */
    .news-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #888;
    }
    .news-brand {
        color: #d32f2f;
        font-weight: 900;
        letter-spacing: -0.5px;
    }
    .news-price {
        color: #ff3333;
        font-weight: bold;
        font-size: 13px;
    }
</style>

<div class="character-guide-container">
<aside>
    <div class="news-sidebar">
        <h3 class="news-sidebar-title"><i class="fas fa-newspaper"></i> Tin Tức & Sự Kiện</h3>
        
        <?php if(empty($recent_articles)): ?>
            <div style="text-align: center; color: #888; font-size: 13px; padding: 10px 0;">
                Chưa có tin tức nào.
            </div>
        <?php else: ?>
            <div>
                <?php foreach($recent_articles as $article): 
                    $img = getFirstImage($article['content']);
                ?>
                <a href="<?= BASE_URL ?>/user/article_detail.php?id=<?= $article['id'] ?>" class="news-item">
                    <div class="news-img-wrapper">
                        <img src="<?= $img ?>" class="news-img" alt="<?= htmlspecialchars($article['title']) ?>">
                    </div>
                    <div class="news-content">
                        <h4 class="news-name"><?= htmlspecialchars($article['title']) ?></h4>
                        <div class="news-meta">
                            <span class="news-brand">UmaCT</span>
                            <span><i class="far fa-clock"></i> <?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="news-sidebar">
        <h3 class="news-sidebar-title"><i class="fas fa-history"></i> Sản phẩm vừa xem</h3>
        
        <?php if (empty($recent_products)): ?>
            <div style="text-align: center; color: #888; font-size: 13px; padding: 20px 0;">
                <i class="fas fa-box-open" style="font-size: 30px; margin-bottom: 10px; color: #ddd;"></i><br>
                Bạn chưa xem sản phẩm nào.
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($recent_products as $rp): 
                    // Gọi hàm mới để lấy ảnh sản phẩm
                    $prod_img = getProductFirstImage($rp);
                ?>
                    <a href="detail.php?id=<?= $rp['id'] ?>" class="news-item">
                        <div class="news-img-wrapper">
                            <img src="<?= htmlspecialchars($prod_img) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" class="news-img">
                        </div>
                        <div class="news-content">
                            <h4 class="news-name"><?= htmlspecialchars($rp['name']) ?></h4>
                            <div class="news-price"><?= number_format($rp['price'], 0, ',', '.') ?>đ</div>
                            <div class="news-meta" style="margin-top: 5px;">
                                <span class="news-brand">UmaCT</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</aside>
</div>