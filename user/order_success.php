<?php 
require_once 'includes/header.php'; 

// 1. SỬA LỖI LẤY ID: Ưu tiên lấy 'orderCode' của PayOS trả về, nếu không có mới lấy 'id' (đơn COD)
$order_id = 0;
if (isset($_GET['orderCode'])) {
    $order_id = (int)$_GET['orderCode'];
} elseif (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
}

// 2. ÉP CẬP NHẬT TRẠNG THÁI NGAY LẬP TỨC DỰA VÀO URL CỦA PAYOS
if ($order_id > 0) {
    // TRƯỜNG HỢP 1: KHÁCH BẤM HỦY TRÊN PAYOS (URL có cancel=true)
    if (isset($_GET['cancel']) && $_GET['cancel'] == 'true') {
        $ch_cancel = curl_init(API_URL . '/orders/' . $order_id . '/status');
        curl_setopt($ch_cancel, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch_cancel, CURLOPT_POSTFIELDS, json_encode(['status' => 'CANCELLED']));
        curl_setopt($ch_cancel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_cancel, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch_cancel);
        curl_close($ch_cancel);
    }
    // TRƯỜNG HỢP 2: KHÁCH ĐÃ QUÉT MÃ VÀ CHUYỂN KHOẢN XONG (URL có status=PAID)
    elseif (isset($_GET['status']) && $_GET['status'] == 'PAID') {
        $ch_paid = curl_init(API_URL . '/orders/' . $order_id . '/status');
        curl_setopt($ch_paid, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch_paid, CURLOPT_POSTFIELDS, json_encode(['status' => 'PAID']));
        curl_setopt($ch_paid, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_paid, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch_paid);
        curl_close($ch_paid);
    }
}
?>

<style>
    .success-container {
        max-width: 600px;
        margin: 50px auto;
        text-align: center;
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: #27ae60;
        color: #fff;
        font-size: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto 25px;
        /* Hiệu ứng nảy nhẹ khi vừa tải trang */
        animation: pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    @keyframes pop {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    .success-title {
        color: #333;
        font-size: 24px;
        margin-bottom: 15px;
    }

    .order-number {
        display: inline-block;
        background: #f9f9f9;
        padding: 10px 20px;
        border: 1px dashed #ddd;
        border-radius: 6px;
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
        color: #ff3333;
        font-size: 18px;
        margin: 15px 0;
    }

    .success-msg {
        color: #666;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .btn-group {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn-home {
        background: #ff3333;
        color: #fff;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-home:hover { background: #e60000; box-shadow: 0 4px 12px rgba(255,51,51,0.3); }

    .btn-history {
        background: #fff;
        color: #555;
        border: 1px solid #ddd;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-history:hover { background: #f5f5f5; border-color: #999; }
</style>

<div class="expanded-mode" style="width: 100%;">
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h2 class="success-title">Đặt hàng thành công!</h2>
        
        <p class="success-msg">
            Cảm ơn bác <strong><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Tùng') ?></strong> đã tin tưởng UmaCT.<br>
            Đơn hàng của bác đã được tiếp nhận và đang trong quá trình xử lý.
        </p>

        <div style="color: #888; font-size: 14px;">Mã đơn hàng của bác là:</div>
        <div class="order-number">#UMACT-ORD-<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></div>

        <p class="success-msg" style="font-size: 13px; margin-top: 10px;">
            Hệ thống đã gửi thông tin chi tiết qua email: <br>
            <span style="color: #333; font-weight: 600;"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></span>
        </p>

        <div class="btn-group">
            <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Về Trang Chủ</a>
            <a href="order_detail.php?id=<?= $order_id ?>" class="btn-history"><i class="fas fa-history"></i> Lịch sử đơn hàng</a>
        </div>
        
        <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; color: #aaa; font-size: 12px;">
            Nếu bác cần hỗ trợ gấp, vui lòng gọi Hotline: <span style="color: #ff3333; font-weight: bold;">0123.456.789</span>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>