<?php
require_once 'includes/header.php';
require_once '../models/product_model.php';
require_once '../models/category_model.php';
require_once '../models/supplier_model.php';

// Gọi thêm CSS
echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/product-detail.css">';

// Kiểm tra ID
if (!isset($_GET['id'])) {
    die("<div class='main-content'><h2>Không tìm thấy sản phẩm!</h2></div>");
}

$id = (int)$_GET['id'];
$product = getProductById($id);

if (!$product) {
    die("<div class='main-content'><h2>Sản phẩm không tồn tại hoặc đã bị xóa.</h2></div>");
}

// Xử lý danh sách ảnh
$images = !empty($product['images']) ? json_decode($product['images'], true) : [];
$main_image = !empty($images) ? $images[0] : 'https://via.placeholder.com/600x600?text=No+Image';

// Lấy tên Danh mục & Nhà cung cấp (Nếu CSDL API 8 chưa JOIN, ta tự đối chiếu)
$categories = getAllCategories();
$suppliers = getAllSuppliers();

$cat_name = "Chưa cập nhật";
foreach($categories as $c) { if($c['id'] == $product['category_id']) $cat_name = $c['name']; }

$sup_name = "Chưa cập nhật";
foreach($suppliers as $s) { if($s['id'] == $product['supplier_id']) $sup_name = $s['name']; }
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
        
        <div class="pd-meta">
            <span>Thương hiệu: <strong><?= htmlspecialchars($sup_name) ?></strong></span>
            <span>|</span>
            <span>Dòng sản phẩm: <strong><?= htmlspecialchars($cat_name) ?></strong></span>
            <span>|</span>
            <span>Mã SP: <strong>#UMACT-<?= $product['id'] ?></strong></span>
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
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="qty-selector">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                    <input type="number" class="qty-input" name="quantity" id="qtyInput" value="1" min="1" max="<?= $product['stock_quantity'] ?>" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
                
                <p style="font-size: 12px; color: #888; margin-bottom: 20px;">(Còn <?= $product['stock_quantity'] ?> sản phẩm trong kho)</p>

                <?php if($product['stock_quantity'] > 0 && $product['is_active']): ?>
                    <button type="submit" class="btn-add-cart">
                        <span class="main-text">THÊM VÀO GIỎ</span>
                        <span class="sub-text">Giao tận nhà - Đổi trả dễ dàng</span>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-add-cart" style="background: #ccc; cursor: not-allowed;">
                        <span class="main-text">TẠM HẾT HÀNG</span>
                    </button>
                <?php endif; ?>
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
</script>

<?php require_once 'includes/footer.php'; ?>