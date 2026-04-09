<?php
require_once '../../admin/includes/header.php';
require_once '../../models/voucher_model.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID mã giảm giá.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$voucher = getVoucherById($id);

if (!$voucher) {
    die("<div style='margin: 20px;'><h2>Lỗi: Mã giảm giá không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'code' => strtoupper(trim($_POST['code'])),
        'discount_amount' => (float)$_POST['discount_amount'],
        'min_order_value' => (float)$_POST['min_order_value'],
        'usage_limit' => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
        // Nối thêm giây để chuẩn định dạng Database
        'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] . ':00' : null
    ];

    if (empty($data['code']) || empty($data['discount_amount'])) {
        $error = "Vui lòng nhập Mã giảm giá và Số tiền giảm!";
    } else {
        try {
            updateVoucher($id, $data);
            $success = "Cập nhật mã giảm giá thành công!";
            // Cập nhật lại mảng hiển thị (loại bỏ phần ':00' để HTML form không bị lỗi hiển thị)
            $voucher = array_merge($voucher, $data);
            if ($voucher['expiration_date']) {
                $voucher['expiration_date'] = substr($voucher['expiration_date'], 0, 16);
            }
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

// Xử lý định dạng ngày giờ để hiển thị vào thẻ <input type="datetime-local">
$formatted_date = '';
if (!empty($voucher['expiration_date'])) {
    // Biến đổi "2026-12-31 23:59:59" thành "2026-12-31T23:59"
    $formatted_date = date('Y-m-d\TH:i', strtotime($voucher['expiration_date']));
}
?>

<div style="margin: 20px;">
    <h2>Sửa Mã giảm giá</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 500px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Mã giảm giá (Code):</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($voucher['code']) ?>" required style="width: 100%; padding: 8px; text-transform: uppercase;">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Số tiền giảm (VNĐ):</label>
                <input type="number" name="discount_amount" class="form-control" value="<?= $voucher['discount_amount'] ?>" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Đơn tối thiểu (VNĐ):</label>
                <input type="number" name="min_order_value" class="form-control" value="<?= $voucher['min_order_value'] ?>" style="width: 100%; padding: 8px;">
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Giới hạn số lần dùng:</label>
                <input type="number" name="usage_limit" class="form-control" value="<?= $voucher['usage_limit'] ?>" style="width: 100%; padding: 8px;" placeholder="Bỏ trống nếu vô hạn">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Hạn sử dụng:</label>
                <input type="datetime-local" name="expiration_date" class="form-control" value="<?= $formatted_date ?>" style="width: 100%; padding: 8px;">
            </div>
        </div>
        
        <button type="submit" class="btn btn-edit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu thay đổi</button>
    </form>
</div>

</body>
</html>