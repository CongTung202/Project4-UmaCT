<?php
require_once 'includes/header.php';
require_once '../models/product_model.php';
require_once '../models/category_model.php';
require_once '../models/supplier_model.php';

echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/product-detail.css">';

if (!isset($_GET['id'])) {
    die("<div class='main-content'><h2>Không tìm thấy sản phẩm!</h2></div>");
}

$id = (int)$_GET['id'];
$product = getProductById($id);

if (!$product) {
    die("<div class='main-content'><h2>Sản phẩm không tồn tại hoặc đã bị xóa.</h2></div>");
}

$images = !empty($product['images']) ? json_decode($product['images'], true) : [];
$main_image = !empty($images) ? $images[0] : 'https://via.placeholder.com/600x600?text=No+Image';

$categories = getAllCategories();
$suppliers = getAllSuppliers();

$cat_name = "Chưa cập nhật";
foreach($categories as $c) { if($c['id'] == $product['category_id']) $cat_name = $c['name']; }

$sup_name = "Chưa cập nhật";
foreach($suppliers as $s) { if($s['id'] == $product['supplier_id']) $sup_name = $s['name']; }

// ==========================================
// THÊM MỚI: LOGIC LẤY SẢN PHẨM LIÊN QUAN
// ==========================================
$all_products = getAllProducts();
$related_products = [];
foreach ($all_products as $p) {
    // Lấy sp cùng danh mục, đang mở bán, và KHÁC với sản phẩm hiện tại
    if ($p['category_id'] == $product['category_id'] && $p['id'] != $product['id'] && $p['is_active'] == 1) {
        $related_products[] = $p;
    }
}
// Chỉ lấy ngẫu nhiên/mới nhất 4 sản phẩm để show cho đẹp
$related_products = array_slice($related_products, 0, 4);
?>

<div class="product-detail-container">
    
    <div class="pd-gallery">
        <div class="main-image-box">
            <img id="mainImage" src="<?= htmlspecialchars($main_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        
        <?php if(count($images) > 1): ?>
        <div class="thumbnail-list">
            <?php foreach($images as $index => $img): ?>
                <div class="thumb-item <?= $index == 0 ? 'active' : '' ?>" onclick="changeImage(this, '<?= htmlspecialchars($img) ?>')">
                    <img src="<?= htmlspecialchars($img) ?>" alt="Thumb">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="pd-info">
        <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="pd-meta" style="font-size: 14px; margin-bottom: 25px;">
            <span style="color: #666;">Thương hiệu: 
                <a href="products.php?supplier=<?= $product['supplier_id'] ?>" style="color: #ff3333; font-weight: bold; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#e60000'" onmouseout="this.style.color='#ff3333'">
                    <?= htmlspecialchars($sup_name) ?>
                </a>
            </span>
            <span style="color: #ccc; margin: 0 12px;">|</span>
            <span style="color: #666;">Dòng sản phẩm: 
                <a href="products.php?category=<?= $product['category_id'] ?>" style="color: #ff3333; font-weight: bold; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#e60000'" onmouseout="this.style.color='#ff3333'">
                    <?= htmlspecialchars($cat_name) ?>
                </a>
            </span>
        </div>

        <div class="pd-price-box">
            <div class="pd-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
            </div>

        <div>
            Trạng thái: 
            <?php if($product['stock_quantity'] > 0 && $product['is_active']): ?>
                <span class="pd-stock"><i class="fas fa-check-circle"></i> Sẵn sàng giao hàng</span>
            <?php else: ?>
                <span class="pd-stock" style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Hết hàng / Ngừng bán</span>
            <?php endif; ?>
        </div>

        <div class="pd-action-box">
            <div style="margin-bottom: 10px; font-weight: bold; font-size: 14px;">Số lượng:</div>
            
            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="qty-selector">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                    <input type="number" class="qty-input" name="quantity" id="qtyInput" value="1" min="1" max="<?= $product['stock_quantity'] ?>" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
                
                <p style="font-size: 12px; color: #888; margin-bottom: 20px;">(Còn <?= $product['stock_quantity'] ?> sản phẩm trong kho)</p>

                <div class="action-group">
                    <?php if($product['stock_quantity'] > 0 && $product['is_active']): ?>
                        <button type="submit" name="action" value="add" class="btn-add-cart">
                             Thêm vào giỏ
                        </button>
                        
                        <button type="submit" name="action" value="buy_now" class="btn-buy-now">
                             Mua ngay
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-buy-now" style="background: #ccc; border-color: #ccc; cursor: not-allowed; flex: 2;">
                            TẠM HẾT HÀNG
                        </button>
                    <?php endif; ?>

                    <button type="button" class="btn-favorite" title="Thêm vào yêu thích" onclick="toggleFavorite(this, <?= $product['id'] ?>)">
                            <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="pd-policies">
        <div class="policy-card">
            <div class="policy-header"><i class="fas fa-shield-alt"></i> Cam kết bán hàng</div>
            <div class="policy-body">
                <ul class="policy-list">
                    <li><i class="fas fa-check"></i> Bảo Đảm Giá Tốt Nhất Trực Tuyến</li>
                    <li><i class="fas fa-check"></i> Hàng chính hãng 100%, đền gấp 10 nếu phát hiện lỗi NSX</li>
                    <li><i class="fas fa-check"></i> FREE SHIPPING toàn quốc đơn hàng trên 500K</li>
                </ul>
            </div>
        </div>

        <div class="policy-card">
            <div class="policy-header" style="background: #27ae60;"><i class="fas fa-info-circle"></i> Lưu ý khi mua hàng</div>
            <div class="policy-body">
                <ul class="policy-list">
                    <li><i class="fas fa-angle-right" style="color: #666;"></i> Khách đọc kỹ ngày phát hành (dự kiến) của sản phẩm.</li>
                    <li><i class="fas fa-angle-right" style="color: #666;"></i> Hàng đặt trước giá có thể thay đổi, inbox fanpage để chốt giá cuối.</li>
                    <li><i class="fas fa-angle-right" style="color: #666;"></i> Khi unbox vui lòng quay video để được hỗ trợ tốt nhất.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pd-description">
    <h3>Thông tin chi tiết</h3>
    <div class="pd-desc-content">
        <?= !empty($product['description']) ? htmlspecialchars($product['description']) : 'Chưa có thông tin mô tả cho sản phẩm này.' ?>
    </div>
</div>
<?php if(!empty($related_products)): ?>
<div class="related-section">
    <div class="related-title">
        <h3>Sản phẩm liên quan</h3>
    </div>
    
    <div class="related-grid">
        <?php foreach($related_products as $rp): ?>
            <a href="detail.php?id=<?= $rp['id'] ?>" style="text-decoration: none;">
                <div class="related-item">
                    <?php 
                        // Kiểm tra ảnh sản phẩm liên quan
                        $rp_img = !empty($rp['main_image']) ? $rp['main_image'] : 'https://via.placeholder.com/200x220?text=No+Image'; 
                    ?>
                    <img src="<?= htmlspecialchars($rp_img) ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                    <div class="r-name"><?= htmlspecialchars($rp['name']) ?></div>
                    <div class="r-price"><?= number_format($rp['price'], 0, ',', '.') ?>đ</div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
    // Đổi ảnh chính khi click Thumbnail
    function changeImage(element, src) {
        document.getElementById('mainImage').src = src;
        
        // Xóa class active của tất cả thumbnail
        let thumbs = document.querySelectorAll('.thumb-item');
        thumbs.forEach(thumb => thumb.classList.remove('active'));
        
        // Thêm class active cho cái vừa click
        element.classList.add('active');
    }

    // Tăng giảm số lượng
    function updateQty(change) {
        let input = document.getElementById('qtyInput');
        let currentVal = parseInt(input.value);
        let maxVal = parseInt(input.getAttribute('max'));
        
        let newVal = currentVal + change;
        
        // Kiểm tra giới hạn (ít nhất 1, nhiều nhất là stock)
        if (newVal >= 1 && newVal <= maxVal) {
            input.value = newVal;
        } else if (newVal > maxVal) {
            alert('Bạn chỉ có thể mua tối đa ' + maxVal + ' sản phẩm!');
        }
    }
   // Hàm Toggle Yêu thích gọi API
    function toggleFavorite(btn, productId) {
        btn.style.opacity = '0.5';

        fetch('ajax_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(res => res.json())
        .then(data => {
            btn.style.opacity = '1';
            
            if (data.status === 'error') {
                // SỬA Ở ĐÂY: Thay alert bằng showToast lỗi
                showToast(data.message, 'error'); 
                
                if(data.message.includes('đăng nhập')) {
                    setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                }
                return;
            }
            
            let icon = btn.querySelector('i');
            if (data.action === 'added') {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('active');
                
                // SỬA Ở ĐÂY: Hiện Toast thành công
                showToast('Đã thêm sản phẩm vào danh sách yêu thích!', 'success');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('active');
                
                // SỬA Ở ĐÂY: Hiện Toast thông báo xóa
                showToast('Đã bỏ yêu thích sản phẩm này.', 'success');
            }
        })
        .catch(err => {
            console.error(err);
            btn.style.opacity = '1';
            showToast('Có lỗi xảy ra, vui lòng thử lại sau.', 'error');
        });
    }
        // Ghi chú: Sau này bác có thể gọi AJAX ở đây để lưu vào Database bảng favorites
</script>

<?php require_once 'includes/footer.php'; ?>
