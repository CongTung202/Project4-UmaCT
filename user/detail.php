<?php
//LƯU COOKIE 
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $viewed_items = isset($_COOKIE['recently_viewed']) ? json_decode($_COOKIE['recently_viewed'], true) : [];
    
    if (($key = array_search($id, $viewed_items)) !== false) {
        unset($viewed_items[$key]);
    }
    array_unshift($viewed_items, $id);
    $viewed_items = array_slice($viewed_items, 0, 5); // Lưu 5 sản phẩm
    setcookie('recently_viewed', json_encode($viewed_items), time() + (86400 * 30), "/");
}
// 

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
$main_image = !empty($images) ? $images[0] : 'https://placehold.co/600x600?text=No+Image';

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
// Lấy danh sách bình luận
$ch_reviews = curl_init(API_URL . '/products/' . $id . '/reviews');
curl_setopt($ch_reviews, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_reviews, CURLOPT_SSL_VERIFYPEER, false);
$res_reviews = curl_exec($ch_reviews);
curl_close($ch_reviews);
$reviews = json_decode($res_reviews, true)['data'] ?? [];
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
                        
                        <button type="button" class="btn-add-cart" onclick="addToCart('add')">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                        
                        <button type="button" class="btn-buy-now" onclick="addToCart('buy_now')">
                            <i class="fas fa-bolt"></i> Mua ngay
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

</div>

<div class="pd-description">
    <h3>Thông tin chi tiết</h3>
    <div class="pd-desc-content">
        <?= !empty($product['description']) ? $product['description'] : 'Chưa có thông tin mô tả cho sản phẩm này.' ?>
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
                        $rp_img = !empty($rp['main_image']) ? $rp['main_image'] : 'https://placehold.co/200x220?text=No+Image'; 
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
    // 2. HÀM XỬ LÝ GIỎ HÀNG (AJAX) - CÁI MÀ BÁC ĐANG BỊ THIẾU
    function addToCart(action) {
        // Lấy ID sản phẩm và số lượng người dùng chọn
        const productId = document.querySelector('input[name="product_id"]').value;
        const quantity = document.getElementById('qtyInput').value;
        
        // Tạo hiệu ứng loading cho nút bấm
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        btn.style.pointerEvents = 'none';

        fetch('ajax_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                product_id: productId, 
                quantity: quantity,
                action: action
            })
        })
        .then(res => res.json())
        .then(data => {
            // Khôi phục nút bấm
            btn.innerHTML = originalHtml;
            btn.style.pointerEvents = 'auto';

            if (data.status === 'success') {
                showToast(data.message, 'success');
                
                // Cập nhật con số màu đỏ trên Header
                const cartBadge = document.getElementById('cart-badge');
                if (cartBadge) {
                    cartBadge.innerText = data.total_items;
                    cartBadge.style.transition = 'transform 0.2s';
                    cartBadge.style.transform = 'scale(1.5)';
                    setTimeout(() => { cartBadge.style.transform = 'scale(1)'; }, 200);
                }

                // Nếu khách bấm "Mua ngay", lập tức chuyển hướng sang trang cart.php
                if (data.action === 'buy_now') {
                    setTimeout(() => { window.location.href = 'cart.php'; }, 500);
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalHtml;
            btn.style.pointerEvents = 'auto';
            showToast('Lỗi kết nối, vui lòng thử lại!', 'error');
        });
    }
</script>
      
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
<!-- ================= PHẦN ĐÁNH GIÁ SẢN PHẨM ================= -->
<div style="margin-top: 50px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Đánh giá sản phẩm (<?= count($reviews) ?>)</h3>

    <!-- Form viết bình luận -->
    <?php if (isset($_SESSION['user'])): ?>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                <b style="font-size: 15px;">Chất lượng sản phẩm:</b>
                <div class="star-rating" id="star-rating" style="color: #ccc; font-size: 20px; cursor: pointer;">
                    <i class="fas fa-star" data-value="1"></i>
                    <i class="fas fa-star" data-value="2"></i>
                    <i class="fas fa-star" data-value="3"></i>
                    <i class="fas fa-star" data-value="4"></i>
                    <i class="fas fa-star" data-value="5"></i>
                </div>
                <input type="hidden" id="review_rating" value="5"> <!-- Mặc định 5 sao -->
            </div>
            
            <textarea id="review_comment" class="form-control" rows="3" placeholder="Xin mời chia sẻ cảm nhận của bác về mô hình này..." style="width: 100%; margin-bottom: 10px; border-radius: 6px; padding: 10px;"></textarea>
            
            <button id="btnSubmitReview" onclick="submitReview()" style="background: #ff3333; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                <i class="fas fa-paper-plane"></i> Gửi đánh giá
            </button>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 20px; background: #fff3f3; color: #ff3333; border-radius: 8px; margin-bottom: 30px; border: 1px dashed #ffb3b3;">
            Vui lòng <a href="login.php" style="font-weight: bold; text-decoration: underline;">Đăng nhập</a> để tham gia bình luận!
        </div>
    <?php endif; ?>

<!-- Danh sách bình luận -->
    <div id="reviews-list">
        <?php if (empty($reviews)): ?>
            <p style="color: #888; text-align: center; font-style: italic;">Chưa có đánh giá nào. Hãy trở thành người đầu tiên review sản phẩm này!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="display: flex; gap: 15px; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                    <!-- Avatar Khách -->
                    <div style="width: 45px; height: 45px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #888; overflow: hidden;">
                        <?= $rev['avatar_url'] ? '<img src="'.$rev['avatar_url'].'" style="width:100%;height:100%;object-fit:cover;">' : '<i class="fas fa-user"></i>' ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <!-- Thông tin khách bình luận -->
                        <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">
                            <?= htmlspecialchars($rev['full_name'] ?: $rev['username']) ?>
                        </div>
                        <div style="color: #ffca28; font-size: 12px; margin-bottom: 8px;">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?= $i <= $rev['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <div style="color: #444; font-size: 14px; line-height: 1.5; margin-bottom: 5px;">
                            <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                        </div>
                        <div style="color: #aaa; font-size: 11px; margin-bottom: 10px;">
                            <?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?>
                        </div>

                        <!-- KHU VỰC HIỂN THỊ CÂU TRẢ LỜI CỦA SHOP -->
                        <?php if (!empty($rev['staff_reply'])): ?>
                            <div style="background: #f1f8e9; border-left: 3px solid #7cb342; padding: 12px 15px; border-radius: 0 8px 8px 0; margin-top: 10px;">
                                <div style="font-weight: bold; font-size: 13px; color: #558b2f; margin-bottom: 5px;">
                                    <i class="fas fa-store"></i> Phản hồi từ UmaCT:
                                </div>
                                <div style="font-size: 13px; color: #333; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($rev['staff_reply'])) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- NẾU CHƯA CÓ TRẢ LỜI VÀ USER LÀ STAFF/ADMIN THÌ HIỆN FORM -->
                            <?php if (isset($_SESSION['user']) && in_array($_SESSION['user']['role_id'], [1, 3])): ?>
                                <button onclick="toggleReplyForm(<?= $rev['id'] ?>)" style="background: none; border: none; color: #3498db; font-size: 12px; font-weight: bold; cursor: pointer; padding: 0;">
                                    <i class="fas fa-reply"></i> Phản hồi
                                </button>
                                
                                <div id="reply-form-<?= $rev['id'] ?>" style="display: none; margin-top: 10px; background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                                    <textarea id="reply-text-<?= $rev['id'] ?>" class="form-control" rows="2" placeholder="Nhập câu trả lời của shop..." style="width: 100%; border-radius: 4px; padding: 8px; font-size: 13px; margin-bottom: 8px;"></textarea>
                                    <button onclick="submitStaffReply(<?= $rev['id'] ?>)" style="background: #3498db; color: white; border: none; padding: 6px 15px; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer;">
                                        Gửi phản hồi
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<script>
// --- LOGIC CHỌN SAO ---
const stars = document.querySelectorAll('#star-rating .fa-star');
const ratingInput = document.getElementById('review_rating');

// Mặc định bôi vàng 5 sao ban đầu
stars.forEach(s => s.style.color = '#ffca28');

stars.forEach(star => {
    star.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        ratingInput.value = value;
        // Bôi màu lại: Số sao <= value thì màu vàng, lớn hơn thì màu xám
        stars.forEach(s => {
            if (s.getAttribute('data-value') <= value) {
                s.style.color = '#ffca28';
                s.classList.replace('far', 'fas');
            } else {
                s.style.color = '#ccc';
                s.classList.replace('fas', 'far'); // Có thể dùng sao rỗng
            }
        });
    });
});

// --- LOGIC GỬI BÌNH LUẬN BẰNG AJAX ---
function submitReview() {
    const comment = document.getElementById('review_comment').value.trim();
    const rating = ratingInput.value;
    const productId = <?= $id ?>; // Lấy ID sản phẩm từ PHP

    if (!comment) {
        showToast('Bác chưa viết cảm nhận kìa!', 'error');
        return;
    }

    const btn = document.getElementById('btnSubmitReview');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    btn.disabled = true;

    fetch('ajax_review.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, rating: rating, comment: comment })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000); // Tải lại trang sau 1s để hiện bình luận
        } else {
            showToast(data.message, 'error');
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi đánh giá';
            btn.disabled = false;
        }
    })
    .catch(err => {
        showToast('Lỗi mạng!', 'error');
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi đánh giá';
        btn.disabled = false;
    });
}
</script>
<script>
// Logic bật/tắt form trả lời
function toggleReplyForm(reviewId) {
    const form = document.getElementById('reply-form-' + reviewId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Logic gửi trả lời bằng AJAX
function submitStaffReply(reviewId) {
    const replyText = document.getElementById('reply-text-' + reviewId).value.trim();
    if (!replyText) {
        showToast('Vui lòng nhập nội dung trả lời!', 'error');
        return;
    }

    fetch('ajax_reply_review.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review_id: reviewId, staff_reply: replyText })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000); // F5 lại để hiện khung xanh
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối!', 'error'));
}
</script>
<?php require_once 'includes/footer.php'; ?>
