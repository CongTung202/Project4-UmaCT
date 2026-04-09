<?php
require_once '../../admin/includes/header.php';
require_once '../../models/user_model.php';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID người dùng.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$userData = getUserDetail($id);

if (!$userData) {
    die("<div style='margin: 20px;'><h2>Lỗi: Người dùng không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$user = $userData['info'];
$orders = $userData['orders'];

// Hàm tiện ích hiển thị trạng thái đơn hàng
function getOrderStatusBadge($status) {
    switch ($status) {
        case 'PENDING': return '<span style="color: #856404; font-weight:bold;">Chờ xử lý</span>';
        case 'PAID': return '<span style="color: #0c5460; font-weight:bold;">Đã thanh toán</span>';
        case 'SHIPPING': return '<span style="color: #004085; font-weight:bold;">Đang giao</span>';
        case 'COMPLETED': return '<span style="color: #155724; font-weight:bold;">Hoàn thành</span>';
        case 'CANCELLED': return '<span style="color: #721c24; font-weight:bold;">Đã hủy</span>';
        default: return $status;
    }
}
?>

<div style="margin: 20px; font-family: Arial, sans-serif;">
    <h2>Hồ sơ Người dùng: <?= htmlspecialchars($user['username']) ?></h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>

    <div style="display: flex; gap: 20px;">
        
        <div style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Thông tin cá nhân</h3>
            
            <div style="margin-bottom: 10px;">
                <strong>ID:</strong> #<?= $user['id'] ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Trạng thái:</strong> 
                <?php if($user['status'] == 'ACTIVE'): ?>
                    <span style="color: green; font-weight: bold;">Đang hoạt động</span>
                <?php else: ?>
                    <span style="color: red; font-weight: bold;">Bị khóa (Banned)</span>
                <?php endif; ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Phân quyền:</strong> <span style="background-color: #e9ecef; padding: 3px 8px; border-radius: 4px;"><?= htmlspecialchars($user['role_name']) ?></span>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Họ và tên:</strong> <?= htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật') ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Số điện thoại:</strong> <?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Địa chỉ mặc định:</strong> <?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Ngày tham gia:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
            </div>
        </div>

        <div style="flex: 2; border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
            <h3 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Lịch sử mua hàng (<?= count($orders) ?> đơn)</h3>
            
            <?php if(empty($orders)): ?>
                <p style="color: #666; font-style: italic;">Người dùng này chưa có đơn hàng nào.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Mã ĐH</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Ngày đặt</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Tổng tiền</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Trạng thái</th>
                            <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Xem chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;"><strong>#<?= $o['id'] ?></strong></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right; color: red; font-weight: bold;">
                                <?= number_format($o['total_price'], 0, ',', '.') ?> đ
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                <?= getOrderStatusBadge($o['status']) ?>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                <a href="../orders/detail.php?id=<?= $o['id'] ?>" style="color: #007bff; text-decoration: none;">Xem ĐH</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
    </div>
</div>

</body>
</html>