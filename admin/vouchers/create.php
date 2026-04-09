<?php
require_once '../../admin/includes/header.php';
require_once '../../models/voucher_model.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'code' => strtoupper(trim($_POST['code'])), // Tự động viết hoa mã giảm giá
        'discount_amount' => (float)$_POST['discount_amount'],
        'min_order_value' => (float)$_POST['min_order_value'],
        'usage_limit' => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
        
        // Cần nối thêm giây ":00" vì HTML5 datetime-local chỉ trả về YYYY-MM-DDTHH:MM
        'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] . ':00' : null
    ];

    if (empty($data['code']) || empty($data['discount_amount'])) {
        $error = "Vui lòng nhập Mã giảm giá và Số tiền giảm!";
    } else {
        try {
            addVoucher($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Thêm Mã giảm giá mới</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 500px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Mã giảm giá (Code):</label>
            <input type="text" name="code" class="form-control" required style="width: 100%; padding: 8px; text-transform: uppercase;" placeholder="VD: UMA100K">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Số tiền giảm (VNĐ):</label>
                <input type="number" name="discount_amount" class="form-control" required style="width: 100%; padding: 8px;" placeholder="VD: 100000">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Đơn tối thiểu (VNĐ):</label>
                <input type="number" name="min_order_value" class="form-control" style="width: 100%; padding: 8px;" placeholder="VD: 500000">
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Giới hạn số lần dùng:</label>
                <input type="number" name="usage_limit" class="form-control" style="width: 100%; padding: 8px;" placeholder="Bỏ trống nếu vô hạn">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Hạn sử dụng:</label>
                <input type="datetime-local" name="expiration_date" class="form-control" style="width: 100%; padding: 8px;">
            </div>
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu mã giảm giá</button>
    </form>
</div>
<?php require_once '../../admin/includes/footer.php'; ?>