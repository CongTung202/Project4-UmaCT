<?php
require_once '../../admin/includes/header.php';
require_once '../../models/product_model.php';

if (!isset($_GET['id'])) die("Thiếu ID sản phẩm");
$id = $_GET['id'];
$product = getProductById($id); // Nhớ viết hàm này trong model nhé

// Giải mã mảng JSON ảnh từ DB
$images = [];
if (!empty($product['images'])) {
    $images = json_decode($product['images'], true);
}
?>

<div style="margin: 20px; font-family: sans-serif;">
    <h2>Chi tiết: <?= htmlspecialchars($product['name']) ?></h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 20px;">Quay lại</a>

    <div style="display: flex; gap: 30px;">
        <div style="flex: 1;">
            <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 10px;">Thư viện ảnh</h3>
            <?php if(empty($images)): ?>
                <p>Sản phẩm này chưa có ảnh.</p>
            <?php else: ?>
                <img id="main-image" src="<?= $images[0] ?>" style="width: 100%; max-height: 400px; object-fit: contain; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px;">
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach($images as $img): ?>
                        <img src="<?= $img ?>" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid transparent; cursor: pointer; border-radius: 4px;" 
                             onclick="document.getElementById('main-image').src = this.src; this.style.borderColor='#007bff';"
                             onmouseout="this.style.borderColor='transparent';">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="flex: 1; background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h3>Thông tin sản phẩm</h3>
            <p><strong>Giá bán:</strong> <span style="color: red; font-size: 20px; font-weight: bold;"><?= number_format($product['price'], 0, ',', '.') ?> đ</span></p>
            <p><strong>Tồn kho:</strong> <?= $product['stock_quantity'] ?> sản phẩm</p>
            <p><strong>Trạng thái:</strong> <?= $product['is_active'] ? 'Đang bán' : 'Ngừng bán' ?></p>
            <hr>
            <h4>Mô tả chi tiết:</h4>
            <div style="white-space: pre-line; line-height: 1.6;">
                <?= $product['description'] ?? 'Chưa có mô tả.' ?>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Tìm tất cả các thẻ oembed do CKEditor tạo ra
    const mediaElements = document.querySelectorAll('oembed[url]');
    
    mediaElements.forEach(el => {
        const url = el.getAttribute('url');
        let iframeSrc = '';
        
        // Kiểm tra xem có phải link YouTube không
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            // Tách lấy ID của video YouTube
            const videoId = url.split(/v\/|v=|youtu\.be\//)[1].split(/[?&]/)[0];
            iframeSrc = `https://www.youtube.com/embed/${videoId}`;
        }
        
        // Nếu lấy được link chuẩn, tạo thẻ iframe để thay thế
        if (iframeSrc) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('src', iframeSrc);
            iframe.setAttribute('width', '100%');
            iframe.setAttribute('height', '450'); // Chiều cao video
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', 'true');
            iframe.style.borderRadius = '12px'; // Bo góc video cho đẹp
            iframe.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            iframe.style.marginTop = '15px';
            iframe.style.marginBottom = '15px';
            
            // Thay thế thẻ oembed vô dụng bằng thẻ iframe xịn xò
            el.parentNode.replaceChild(iframe, el);
        }
    });
});
</script>

<style>
    /* Chỉnh lại thẻ figure bọc ngoài video để nó căn giữa và tràn viền đẹp mắt */
    .product-description-content figure.media {
        margin: 20px 0;
        width: 100%;
        text-align: center;
    }
</style>
<?php require_once '../../admin/includes/footer.php'; ?>