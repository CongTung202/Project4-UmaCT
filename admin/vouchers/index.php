<?php
require_once '../../admin/includes/header.php';
require_once '../../models/voucher_model.php';

$vouchers = getAllVouchers();
?>

<div style="margin: 20px;">
    <h2>Quản lý Mã giảm giá (Vouchers)</h2>
    <a href="create.php" class="btn btn-add">+ Thêm mã mới</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã (Code)</th>
                <th>Mức giảm</th>
                <th>Đơn tối thiểu</th>
                <th>Giới hạn dùng</th>
                <th>Hạn sử dụng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($vouchers)): ?>
                <tr><td colspan="7" style="text-align:center;">Chưa có mã giảm giá nào.</td></tr>
            <?php else: ?>
                <?php foreach ($vouchers as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><strong style="color: #e83e8c; font-size: 16px;"><?= htmlspecialchars($v['code']) ?></strong></td>
                    <td><?= number_format($v['discount_amount'], 0, ',', '.') ?> đ</td>
                    <td><?= number_format($v['min_order_value'], 0, ',', '.') ?> đ</td>
                    <td><?= $v['usage_limit'] ?? 'Không giới hạn' ?></td>
                    <td>
                        <?php 
                            if ($v['expiration_date']) {
                                // Định dạng lại ngày giờ hiển thị cho đẹp
                                echo date('d/m/Y H:i', strtotime($v['expiration_date']));
                            } else {
                                echo "Vô thời hạn";
                            }
                        ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $v['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $v['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa mã này?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>