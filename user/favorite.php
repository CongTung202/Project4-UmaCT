<?php 
require_once 'includes/header.php'; 
require_once '../models/favorite_model.php';

// Bắt buộc phải đăng nhập mới có danh sách yêu thích
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$favorites = getUserFavorites($_SESSION['user']['id']);
?>

<style>
    .fav-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
        gap: 25px; 
    }
    .fav-item { 
        background: #fff; 
        border-radius: 8px; 
        padding: 15px; 
        text-align: center; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        position: relative; 
        transition: all 0.3s ease; 
    }
    .fav-item:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
    }
    .fav-img { 
        width: 100%; 
        height: 200px; 
        object-fit: contain; 
        margin-bottom: 15px; 
    }
    .fav-name { 
        font-size: 14px; 
        font-weight: 600; 
        color: #333; 
        margin-bottom: 10px; 
        display: -webkit-box; 
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical; 
        overflow: hidden; 
    }
    .fav-price { 
        color: #ff3333; 
        font-weight: bold; 
        font-size: 16px; 
        margin-bottom: 15px; 
    }
    .btn-remove-fav { 
        position: absolute; 
        top: 10px; right: 10px; 
        background: #fff; 
        border: 1px solid #ddd; 
        color: #999; 
        border-radius: 50%; 
        width: 32px; height: 32px; 
        cursor: pointer; 
        transition: 0.3s;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-remove-fav:hover { 
        color: #ff3333; 
        border-color: #ff3333; 
        background: #ffe0e0;
    }
    .btn-view { 
        display: block; 
        background: #ff3333; 
        color: white; 
        text-decoration: none; 
        padding: 10px; 
        border-radius: 6px; 
        font-size: 13px; 
        font-weight: bold; 
        transition: 0.3s;
    }
    .btn-view:hover { background: #e60000; color: white; }
</style>

<!-- Sử dụng width: 100% để khắc phục lỗi lệch lề form -->
<div style="width: 100%;"> 
    <h2 style="margin-bottom: 25px;"><i class="fas fa-heart" style="color: #ff3333;"></i> Bộ sưu tập Yêu thích</h2>
    
    <?php if (empty($favorites)): ?>
        <div style="text-align: center; padding: 100px 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <i class="far fa-heart" style="font-size: 60px; color: #eee; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 15px;">Bác chưa có sản phẩm yêu thích nào!</h3>
            <a href="products.php" style="display: inline-block; background: #ff3333; color: #fff; padding: 10px 25px; border-radius: 30px; text-decoration: none; font-weight: bold;">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <div class="fav-grid">
            <?php foreach ($favorites as $f): ?>
                <div class="fav-item" id="fav-item-<?= $f['id'] ?>">
                    <!-- Nút xóa (Gọi hàm JS ở dưới) -->
                    <button class="btn-remove-fav" onclick="removeFavorite(<?= $f['id'] ?>)" title="Bỏ yêu thích">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <a href="detail.php?id=<?= $f['id'] ?>">
                        <img src="<?= $f['main_image'] ?>" class="fav-img" alt="<?= htmlspecialchars($f['name']) ?>">
                    </a>
                    
                    <a href="detail.php?id=<?= $f['id'] ?>" style="text-decoration: none;">
                        <div class="fav-name"><?= htmlspecialchars($f['name']) ?></div>
                    </a>
                    
                    <div class="fav-price"><?= number_format($f['price'], 0, ',', '.') ?>đ</div>
                    
                    <a href="detail.php?id=<?= $f['id'] ?>" class="btn-view">
                        <i class="fas fa-shopping-cart"></i> Xem / Mua ngay
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Hàm gỡ bỏ sản phẩm yêu thích ngay trên giao diện mà không cần F5
function removeFavorite(productId) {
    if(!confirm('Bác có chắc muốn bỏ mô hình này khỏi danh sách yêu thích?')) return;
    
    fetch('ajax_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
        // Dựa vào logic xử lý toggle của bác trong ajax_favorite.php
        if (data.status === 'success') {
            showToast('Đã bỏ yêu thích sản phẩm!', 'success');
            
            // Lấy khối div chứa sản phẩm đó ra
            const item = document.getElementById('fav-item-' + productId);
            
            // Làm mờ dần (Animation)
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            
            // Sau 300ms thì xóa hẳn nó đi
            setTimeout(() => { 
                item.remove(); 
                
                // Nếu xóa hết sạch thì tự tải lại trang để hiện cái thông báo trống
                if(document.querySelectorAll('.fav-item').length === 0) {
                    window.location.reload();
                }
            }, 300);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Lỗi kết nối, vui lòng thử lại!', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>