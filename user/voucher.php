<?php 
require_once 'includes/header.php'; 
require_once '../models/voucher_model.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$available_vouchers = getAvailableVouchers();
$used_vouchers = getUserVoucherHistory($user_id);
?>

<style>
    .voucher-tabs { display: flex; gap: 20px; margin-bottom: 30px; border-bottom: 2px solid #eee; }
    .tab-item { padding: 10px 20px; cursor: pointer; font-weight: bold; color: #888; border-bottom: 3px solid transparent; }
    .tab-item.active { color: #ff3333; border-bottom-color: #ff3333; }

    .voucher-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    
    /* Thiết kế thẻ Voucher kiểu vé xé */
    .voucher-card { 
        background: #fff; display: flex; border-radius: 10px; overflow: hidden; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; border: 1px solid #eee;
    }
    .voucher-left { 
        background: #ff3333; color: #fff; padding: 20px; width: 100px; 
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border-right: 2px dashed #fff; position: relative;
    }
    /* Hiệu ứng vết cắt vé xé */
    .voucher-left::before, .voucher-left::after {
        content: ''; position: absolute; right: -6px; width: 12px; height: 12px; background: #f4f7f8; border-radius: 50%;
    }
    .voucher-left::before { top: -6px; } .voucher-left::after { bottom: -6px; }

    .voucher-right { padding: 15px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
    .v-code { font-weight: 800; color: #333; font-size: 18px; margin-bottom: 5px; }
    .v-desc { font-size: 13px; color: #666; margin-bottom: 10px; }
    .v-date { font-size: 11px; color: #999; }
    
    .btn-copy { 
        background: #fff; color: #ff3333; border: 1px solid #ff3333; padding: 5px 12px; 
        border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: bold; transition: 0.3s;
    }
    .btn-copy:hover { background: #ff3333; color: #fff; }
    .used-badge { background: #e0e0e0; color: #888; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
</style>

<div style="width: 100%;">
    <h2 style="margin-bottom: 25px;"><i class="fas fa-ticket-alt" style="color: #ff3333;"></i> Kho Ưu Đãi Của Tôi</h2>

    <div class="voucher-tabs">
        <div class="tab-item active" onclick="switchTab('available')">Mã ưu đãi đang có</div>
        <div class="tab-item" onclick="switchTab('history')">Lịch sử sử dụng</div>
    </div>

    <div id="tab-available" class="voucher-content">
        <?php if (empty($available_vouchers)): ?>
            <p style="text-align: center; color: #888; padding: 50px;">Hiện chưa có mã giảm giá nào mới.</p>
        <?php else: ?>
            <div class="voucher-grid">
                <?php foreach ($available_vouchers as $v): ?>
                    <div class="voucher-card">
                        <div class="voucher-left">
                            <i class="fas fa-gift" style="font-size: 24px; margin-bottom: 5px;"></i>
                            <div style="font-size: 11px; font-weight: bold;">GIẢM GIÁ</div>
                        </div>
                        <div class="voucher-right">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div class="v-code"><?= $v['code'] ?></div>
                                <button class="btn-copy" onclick="copyCode('<?= $v['code'] ?>')">SAO CHÉP</button>
                            </div>
                            <div class="v-desc">Giảm <b><?= number_format($v['discount_amount'], 0, ',', '.') ?>đ</b> cho đơn từ <?= number_format($v['min_order_value'], 0, ',', '.') ?>đ</div>
                            <div class="v-date">Hết hạn: <?= date('d/m/Y', strtotime($v['expiration_date'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="tab-history" class="voucher-content" style="display: none;">
        <?php if (empty($used_vouchers)): ?>
            <p style="text-align: center; color: #888; padding: 50px;">Bác chưa sử dụng mã giảm giá nào.</p>
        <?php else: ?>
            <div class="voucher-grid">
                <?php foreach ($used_vouchers as $v): ?>
                    <div class="voucher-card" style="opacity: 0.7;">
                        <div class="voucher-left" style="background: #999;">
                            <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                        </div>
                        <div class="voucher-right">
                            <div style="display: flex; justify-content: space-between;">
                                <div class="v-code" style="text-decoration: line-through;"><?= $v['code'] ?></div>
                                <span class="used-badge">ĐÃ DÙNG</span>
                            </div>
                            <div class="v-desc">Đã dùng cho đơn hàng #ORD-<?= $v['order_id'] ?></div>
                            <div class="v-date">Sử dụng ngày: <?= date('d/m/Y', strtotime($v['used_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.voucher-content').forEach(c => c.style.display = 'none');
    
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tabName).style.display = 'block';
}

function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('Đã sao chép mã: ' + code, 'success');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>