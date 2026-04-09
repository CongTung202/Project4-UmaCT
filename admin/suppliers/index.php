<?php
require_once '../../admin/includes/header.php';
require_once '../../models/supplier_model.php';

$suppliers = getAllSuppliers();
?>

<div style="margin: 20px;">
    <h2>Quản lý Nhà cung cấp</h2>
    <a href="create.php" class="btn btn-add">+ Thêm nhà cung cấp</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên nhà cung cấp</th>
                <th>Thông tin liên hệ</th>
                <th>Địa chỉ</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($suppliers)): ?>
                <tr><td colspan="5" style="text-align:center;">Chưa có nhà cung cấp nào.</td></tr>
            <?php else: ?>
                <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><?= htmlspecialchars($s['contact_info'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($s['address'] ?? 'N/A') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $s['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>