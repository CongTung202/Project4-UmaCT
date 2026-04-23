<?php 
require_once 'includes/header.php'; 
require_once '../models/cart_model.php';
require_once '../models/product_model.php';

echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/cart.css">';

// 1. LẤY DỮ LIỆU GIỎ HÀNG
$cart_items = [];
if (isset($_SESSION['user'])) {
    // Lấy từ Database (Hàm bác đã tạo ở bước trước)
    $cart_items = getCartFromDB($_SESSION['user']['id']);
} else {
    // Lấy từ Session cho khách vãng lai
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            $p = getProductById($p_id);
            if ($p) {
                $images = json_decode($p['images'], true);
                $p['main_image'] = !empty($images) ? $images[0] : '';
                $p['quantity'] = $qty;
                $cart_items[] = $p;
            }
        }
    }
}
?>

<div class="main-content">
    <h2 style="margin-bottom: 20px;">Giỏ hàng của bạn (<?= count($cart_items) ?> sản phẩm)</h2>

    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 100px 0; background: #fff; border-radius: 8px;">
            <i class="fas fa-shopping-basket" style="font-size: 60px; color: #eee; margin-bottom: 20px;"></i>
            <h3 style="color: #888;">Giỏ hàng của bạn đang trống!</h3>
            <a href="products.php" style="color: #ff3333; text-decoration: none; font-weight: bold;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-main">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th width="5%"><input type="checkbox" id="selectAll" class="cart-checkbox" checked></th>
                            <th width="50%">Sản phẩm</th>
                            <th width="15%">Đơn giá</th>
                            <th width="15%">Số lượng</th>
                            <th width="15%">Thành tiền</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                        <tr class="cart-item" data-id="<?= $item['id'] ?>">
                            <td>
                                <input type="checkbox" class="cart-checkbox item-check" checked 
                                       data-price="<?= $item['price'] ?>" 
                                       data-qty="<?= $item['quantity'] ?>">
                            </td>
                            <td>
                                <div class="cart-product-info">
                                    <img src="<?= $item['main_image'] ?>" alt="Product">
                                    <span class="cart-product-name"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                            </td>
                            <td><span class="cart-price"><?= number_format($item['price'], 0, ',', '.') ?>đ</span></td>
                            <td>
                                <div class="qty-selector" style="transform: scale(0.8); margin: 0;">
                                    <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" readonly style="background: none;">
                                </div>
                            </td>
                            <td><span class="cart-price item-total"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</span></td>
                            <td>
                                <button class="btn-remove" title="Xóa khỏi giỏ"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <h3 style="margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px;">Tạm tính</h3>
                
                <div class="summary-row">
                    <span>Sản phẩm đã chọn:</span>
                    <span id="selectedCount">0</span>
                </div>
                
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color: #27ae60;">Miễn phí</span>
                </div>

                <div class="summary-row" style="margin-top: 30px; padding-top: 20px; border-top: 2px dashed #eee;">
                    <span style="font-weight: bold;">Tổng cộng:</span>
                    <span class="total-price" id="finalTotal">0đ</span>
                </div>

                <button class="btn-checkout">Tiến hành đặt hàng</button>
                <p style="font-size: 12px; color: #999; text-align: center; margin-top: 15px;">(Giá đã bao gồm VAT nếu có)</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const itemChecks = document.querySelectorAll('.item-check');
    const finalTotal = document.getElementById('finalTotal');
    const selectedCount = document.getElementById('selectedCount');

    // Hàm tính toán lại tổng tiền
    function calculateTotal() {
        let total = 0;
        let count = 0;

        itemChecks.forEach(check => {
            if (check.checked) {
                const price = parseFloat(check.dataset.price);
                const qty = parseInt(check.dataset.qty);
                total += price * qty;
                count++;
            }
        });

        // Định dạng tiền Việt Nam
        finalTotal.innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        selectedCount.innerText = count;
    }

    // Sự kiện khi nhấn Checkbox tổng
    if(selectAll) {
        selectAll.addEventListener('change', function() {
            itemChecks.forEach(check => {
                check.checked = this.checked;
            });
            calculateTotal();
        });
    }

    // Sự kiện khi nhấn từng Checkbox con
    itemChecks.forEach(check => {
        check.addEventListener('change', function() {
            // Nếu có 1 cái bỏ check thì cái "Chọn tất cả" cũng phải tắt theo
            if (!this.checked) selectAll.checked = false;
            
            // Nếu tất cả đều check thì "Chọn tất cả" phải bật lên
            const allChecked = Array.from(itemChecks).every(i => i.checked);
            if (allChecked) selectAll.checked = true;

            calculateTotal();
        });
    });

    // Chạy tính toán lần đầu khi load trang
    calculateTotal();
});
</script>

<?php require_once 'includes/footer.php'; ?>