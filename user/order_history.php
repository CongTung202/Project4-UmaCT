<?php 
require_once 'includes/header.php'; 
require_once '../models/order_model.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$orders = getUserOrders($_SESSION['user']['id']);

// Hàm hỗ trợ hiển thị trạng thái bằng tiếng Việt và màu sắc
function getStatusLabel($status) {
    $labels = [
        'PENDING'   => ['text' => 'Chờ xử lý', 'color' => '#f39c12'],
        'PAID'      => ['text' => 'Đã thanh toán', 'color' => '#27ae60'],
        'SHIPPING'  => ['text' => 'Đang giao hàng', 'color' => '#3498db'],
        'COMPLETED' => ['text' => 'Hoàn thành', 'color' => '#2ecc71'],
        'CANCELLED' => ['text' => 'Đã hủy', 'color' => '#e74c3c']
    ];
    return $labels[$status] ?? ['text' => $status, 'color' => '#95a5a6'];
}
?>

<div style="width: 100%;">
    <h2 style="margin-bottom: 25px;"><i class="fas fa-history"></i> Lịch sử mua hàng</h2>

    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 50px;">
                <p style="color: #888;">Bác chưa có đơn hàng nào!</p>
                <a href="products.php" style="color: #ff3333; font-weight: bold;">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 15px;">Mã đơn hàng</th>
                        <th style="padding: 15px;">Ngày đặt</th>
                        <th style="padding: 15px;">Tổng tiền</th>
                        <th style="padding: 15px;">Trạng thái</th>
                        <th style="padding: 15px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <?php $st = getStatusLabel($o['status']); ?>
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding: 15px; font-weight: bold;">#ORD-<?= $o['id'] ?></td>
                            <td style="padding: 15px; color: #666;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td style="padding: 15px; font-weight: bold; color: #ff3333;"><?= number_format($o['total_price'], 0, ',', '.') ?>đ</td>
                            <td style="padding: 15px;">
                                <span style="background: <?= $st['color'] ?>; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                    <?= $st['text'] ?>
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="order_detail.php?id=<?= $o['id'] ?>" style="color: #3498db; text-decoration: none; font-size: 14px; font-weight: bold;">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>