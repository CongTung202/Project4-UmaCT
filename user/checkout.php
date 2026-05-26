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


$checkout_items = [];
$total_bill = 0;

// KIỂM TRA: NẾU LÀ "MUA NGAY" TỪ TRANG CHI TIẾT
if (isset($_POST['is_direct_buy']) && $_POST['is_direct_buy'] == '1') {
    $id = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    
    $p = getProductById($id);
    if ($p) {
        $p['quantity'] = $qty;
        
        // Lấy ảnh đại diện từ chuỗi JSON của API Python
        $images = !empty($p['images']) ? json_decode($p['images'], true) : [];
        $p['main_image'] = !empty($images) ? $images[0] : 'https://placehold.co/100x100?text=No+Image';
        
        $checkout_items[] = $p;
        $total_bill += ($p['price'] * $qty);
    }
} 
// KIỂM TRA: NẾU ĐI TỪ GIỎ HÀNG SANG (Như cũ)
else {
    $selected_ids = $_POST['selected_items'] ?? []; 
    if (empty($selected_ids)) {
        echo "<script>alert('Vui lòng chọn sản phẩm trong giỏ hàng trước!'); window.location.href='cart.php';</script>";
        exit;
    }

    foreach ($selected_ids as $id) {
        $p = getProductById($id);
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
}

// Chốt chặn an toàn: Tránh trường hợp mảng rỗng
if (empty($checkout_items)) {
    echo "<script>alert('Sản phẩm không hợp lệ!'); window.location.href='index.php';</script>";
    exit;
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cart.css">
<style>
    .checkout-form { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
</style>

<div class=" expanded-mode">
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
                        <label style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px; cursor: pointer;">
    <input type="radio" name="payment" value="3">
    <span>VietQR (PayOS)</span>
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
                            <div style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($item['name']) ?></div>
                            <div style="font-size: 12px; color: #888;">x<?= $item['quantity'] ?></div>
                        </div>
                        <div style="font-weight: bold; color: #ff3333;"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin: 20px 0; border-top: 1px dashed #eee; padding-top: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px;">
                    <i class="fas fa-ticket-alt" style="color: #ff3333;"></i> Nhập mã giảm giá:
                </label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="voucher_code" placeholder="VD: UMA100K" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; text-transform: uppercase;">
                    <button type="button" id="btnApplyVoucher" style="background: #333; color: #fff; border: none; padding: 0 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Áp dụng</button>
                </div>
                <div id="voucher_msg" style="font-size: 12px; margin-top: 8px; min-height: 18px;"></div>
            </div>

            <div class="summary-row" style="border-top: 2px dashed #eee; padding-top: 20px;">
                <span style="font-weight: bold;">Tổng tiền thanh toán:</span>
                <span class="total-price" id="final_price_display"><?= number_format($total_bill, 0, ',', '.') ?>đ</span>
            </div>

            <button type="button" class="btn-checkout" id="btnPlaceOrder">ĐẶT HÀNG NGAY</button>
        </div>
    </div>
</div>

<script>
// BIẾN TOÀN CỤC CHO VOUCHER
let appliedVoucherId = null;
let finalDiscount = 0;
const baseTotal = <?= $total_bill ?>;

// 1. XỬ LÝ NÚT ÁP DỤNG VOUCHER
document.getElementById('btnApplyVoucher').addEventListener('click', function() {
    const code = document.getElementById('voucher_code').value.trim();
    if(!code) return showToast('Bác chưa nhập mã kìa!', 'error');

    this.innerText = '...';
    this.disabled = true;

    fetch('ajax_apply_voucher.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code, cart_total: baseTotal })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('btnApplyVoucher').innerText = 'Áp dụng';
        document.getElementById('btnApplyVoucher').disabled = false;
        
        const msgBox = document.getElementById('voucher_msg');
        
        if (data.status === 'success') {
            appliedVoucherId = data.data.voucher_id;
            finalDiscount = data.data.discount_amount;
            
            // Hiện thông báo xanh và đổi tổng tiền
            msgBox.innerHTML = `<span style="color: #27ae60; font-weight: bold;"><i class="fas fa-check-circle"></i> Giảm ${new Intl.NumberFormat('vi-VN').format(finalDiscount)}đ thành công!</span>`;
            
            let newTotal = baseTotal - finalDiscount;
            if (newTotal < 0) newTotal = 0;
            document.getElementById('final_price_display').innerText = new Intl.NumberFormat('vi-VN').format(newTotal) + 'đ';
            document.getElementById('voucher_code').disabled = true; // Khóa ô nhập liệu
        } else {
            appliedVoucherId = null;
            finalDiscount = 0;
            msgBox.innerHTML = `<span style="color: #ff3333;"><i class="fas fa-exclamation-circle"></i> ${data.message}</span>`;
            document.getElementById('final_price_display').innerText = new Intl.NumberFormat('vi-VN').format(baseTotal) + 'đ';
        }
    })
    .catch(err => {
        document.getElementById('btnApplyVoucher').innerText = 'Áp dụng';
        document.getElementById('btnApplyVoucher').disabled = false;
        showToast('Lỗi mạng khi kiểm tra mã!', 'error');
    });
});

// 2. XỬ LÝ NÚT ĐẶT HÀNG (ĐÃ FIX LỖI)
const btnPlaceOrder = document.getElementById('btnPlaceOrder');

if (btnPlaceOrder) {
    btnPlaceOrder.addEventListener('click', function() {
        let currentTotal = baseTotal - finalDiscount;
        if (currentTotal < 0) currentTotal = 0;

        // Lấy phương thức thanh toán an toàn
        const paymentMethodEl = document.querySelector('input[name="payment"]:checked');
        const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 1;

        const data = {
            user_id: <?= $user['id'] ?>,
            full_name: document.getElementById('full_name').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            address: document.getElementById('address').value.trim(),
            total_price: currentTotal, 
            payment_method: paymentMethod,
            items: <?= json_encode($checkout_items) ?>,
            voucher_id: appliedVoucherId
        };

        if(!data.full_name || !data.address || !data.phone) {
            showToast('Vui lòng điền đầy đủ thông tin giao hàng!', 'error');
            return;
        }

        // Đổi trạng thái nút bấm
        btnPlaceOrder.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        btnPlaceOrder.disabled = true;

        fetch('process_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
            const orderId = res.order_id;
            
            // NẾU LÀ COD (payment_method = 1) -> Chuyển sang trang thành công luôn
            if (data.payment_method == 1) {
                showToast('Đặt hàng thành công! Đang chuyển hướng...', 'success');
                setTimeout(() => { window.location.href = 'order_success.php?id=' + orderId; }, 1500);
            } 
            // NẾU LÀ PAYOS (payment_method = 3) -> Gọi Python lấy link quét QR
            else if (data.payment_method == 3) {
                showToast('Đang tạo mã QR thanh toán...', 'success');
                fetch(`http://127.0.0.1:8000/api/payments/payos/create-link/${orderId}`, {
                    method: 'POST'
                })
                .then(payosRes => payosRes.json())
                .then(payosData => {
                    if(payosData.status === 'success') {
                        // Chuyển hướng khách hàng sang cổng thanh toán của PayOS
                        window.location.href = payosData.checkoutUrl;
                    } else {
                        showToast('Lỗi tạo link thanh toán!', 'error');
                        btnPlaceOrder.disabled = false;
                        btnPlaceOrder.innerHTML = 'ĐẶT HÀNG NGAY';
                    }
                });
            }
            } else {
                showToast(res.message, 'error');
                // Sửa lỗi "Cannot set properties of null" ở đây
                btnPlaceOrder.disabled = false;
                btnPlaceOrder.innerText = 'ĐẶT HÀNG NGAY';
            }
        })
        .catch(err => {
            console.error("Lỗi mạng:", err);
            showToast('Có lỗi xảy ra, vui lòng thử lại!', 'error');
            btnPlaceOrder.disabled = false;
            btnPlaceOrder.innerText = 'ĐẶT HÀNG NGAY';
        });
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>