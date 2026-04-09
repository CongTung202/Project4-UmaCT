<?php
require_once '../../admin/includes/header.php';
require_once '../../models/order_model.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID đơn hàng.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$orderData = getOrderDetail($id);

if (!$orderData) {
    die("<div style='margin: 20px;'><h2>Lỗi: Đơn hàng không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$order = $orderData['order_info'];
$items = $orderData['items'];

// Xử lý khi Admin nhấn nút Cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    try {
        updateOrderStatus($id, $new_status);
        $success = "Cập nhật trạng thái đơn hàng thành công!";
        $order['status'] = $new_status; // Cập nhật lại biến để hiển thị ngay lập tức
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<div style="margin: 20px; font-family: Arial, sans-serif;">
    <h2>Chi tiết Đơn hàng #<?= $order['id'] ?></h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>

    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $error ?></div>
    <?php endif; ?>

    <div style="display: flex; gap: 20px;">
        <div style="flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
            <h3>Thông tin người mua</h3>
            <p><strong>Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'Không có') ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone'] ?? 'Không có') ?></p>
            <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
            <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
            
            <hr>
            
            <h3>Cập nhật trạng thái</h3>
            <form action="" method="POST" style="display: flex; gap: 10px; align-items: center;">
                <select name="status" class="form-control" style="width: 200px;">
                    <option value="PENDING" <?= $order['status'] == 'PENDING' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="PAID" <?= $order['status'] == 'PAID' ? 'selected' : '' ?>>Đã thanh toán (PayOS)</option>
                    <option value="SHIPPING" <?= $order['status'] == 'SHIPPING' ? 'selected' : '' ?>>Đang giao hàng</option>
                    <option value="COMPLETED" <?= $order['status'] == 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                    <option value="CANCELLED" <?= $order['status'] == 'CANCELLED' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
                <button type="submit" class="btn btn-edit">Cập nhật</button>
            </form>
        </div>

        <div style="flex: 2; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
            <h3>Sản phẩm trong đơn hàng</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Tên sản phẩm</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Số lượng</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Đơn giá lúc mua</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;"><?= number_format($item['price_at_purchase'], 0, ',', '.') ?> đ</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right; color: red; font-weight: bold;">
                            <?= number_format($item['quantity'] * $item['price_at_purchase'], 0, ',', '.') ?> đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; text-align: right; font-size: 18px;">
                Tổng cộng thanh toán: <strong style="color: red; font-size: 24px;"><?= number_format($order['total_price'], 0, ',', '.') ?> đ</strong>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../admin/includes/footer.php'; ?>