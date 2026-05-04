<?php 
require_once 'includes/header.php'; 
require_once '../models/cart_model.php';
require_once '../models/product_model.php';

// Kiểm tra đăng nhập (Bắt buộc phải đăng nhập để lưu thông tin như bác yêu cầu)
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập để tiến hành thanh toán!'); window.location.href='login.php';</script>";
    exit;
}

// Lấy thông tin user hiện tại từ Database để pre-fill (điền sẵn)
$user = $_SESSION['user']; 

// Nhận danh sách ID sản phẩm được chọn từ giỏ hàng (Gửi qua POST hoặc lấy từ Session đã lọc)
$selected_ids = $_POST['selected_items'] ?? []; 
if (empty($selected_ids)) {
    echo "<script>alert('Vui lòng chọn sản phẩm trong giỏ hàng trước!'); window.location.href='cart.php';</script>";
    exit;
}

$checkout_items = [];
$total_bill = 0;

foreach ($selected_ids as $id) {
    $p = getProductById($id);
    // Ở đây bác cần lấy thêm số lượng từ Cart của User này
    $cart_data = getCartFromDB($user['id']);
    foreach($cart_data as $c) {
        if($c['id'] == $id) {
            $p['quantity'] = $c['quantity'];
            $p['main_image'] = $c['main_image'];
            $checkout_items[] = $p;
            $total_bill += ($p['price'] * $p['quantity']);
        }
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cart.css">
<style>
    .checkout-form { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
</style>

<div class="main-content expanded-mode">
    <h2 style="margin-bottom: 30px;">Xác nhận đặt hàng</h2>
    
    <div class="cart-container">
        <div class="cart-main">
            <div class="checkout-form">
                <h3 style="margin-bottom: 20px; color: #ff3333;"><i class="fas fa-map-marker-alt"></i> Thông tin giao hàng</h3>
                <form id="orderForm">
                    <div class="form-group">
                        <label>Họ và tên người nhận</label>
                        <input type="text" id="full_name" class="form-control" placeholder="Nhập họ tên..." value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" id="phone" class="form-control" placeholder="Số điện thoại liên hệ..." value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ nhận hàng</label>
                        <textarea id="address" class="form-control" rows="3" placeholder="Số nhà, tên đường, phường/xã..." required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <h3 style="margin-bottom: 15px;"><i class="fas fa-credit-card"></i> Phương thức thanh toán</h3>
                        <label style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="payment" value="1" checked>
                            <span>Thanh toán khi nhận hàng (COD)</span>
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <div class="cart-summary">
            <h3 style="margin-bottom: 20px;">Đơn hàng của bạn</h3>
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                <?php foreach($checkout_items as $item): ?>
                    <div style="display: flex; gap: 10px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f9f9f9;">
                        <img src="<?= $item['main_image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <div style="flex: 1;">
                            <div style="font-size: 13px; font-weight: 600;"><?= $item['name'] ?></div>
                            <div style="font-size: 12px; color: #888;">x<?= $item['quantity'] ?></div>
                        </div>
                        <div style="font-weight: bold; color: #ff3333;"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-row" style="border-top: 2px dashed #eee; padding-top: 20px;">
                <span style="font-weight: bold;">Tổng tiền thanh toán:</span>
                <span class="total-price"><?= number_format($total_bill, 0, ',', '.') ?>đ</span>
            </div>

            <button type="button" class="btn-checkout" id="btnPlaceOrder">ĐẶT HÀNG NGAY</button>
        </div>
    </div>
</div>

<script>
document.getElementById('btnPlaceOrder').addEventListener('click', function() {
    const data = {
        user_id: <?= $user['id'] ?>,
        full_name: document.getElementById('full_name').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        total_price: <?= $total_bill ?>,
        payment_method: document.querySelector('input[name=\"payment\"]:checked').value,
        items: <?= json_encode($checkout_items) ?>
    };

    if(!data.full_name || !data.address || !data.phone) {
        showToast('Vui lòng điền đầy đủ thông tin giao hàng!', 'error');
        return;
    }

    this.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Đang xử lý...';
    this.disabled = true;

    // Gọi AJAX xử lý đặt hàng
    fetch('process_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            showToast('Đặt hàng thành công! Đang chuyển hướng...', 'success');
            setTimeout(() => { window.location.href = 'order_success.php?id=' + res.order_id; }, 2000);
        } else {
            showToast(res.message, 'error');
            this.disabled = false;
            this.innerText = 'ĐẶT HÀNG NGAY';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>