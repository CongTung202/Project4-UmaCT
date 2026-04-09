<?php
require_once '../../admin/includes/header.php';
require_once '../../models/product_model.php';

$products = getAllProducts();
?>

<div style="margin: 20px;">
    <h2>Danh sách Sản phẩm</h2>
    <a href="create.php" class="btn btn-add">+ Thêm sản phẩm mới</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Nhà cung cấp</th>
                <th>Giá (VNĐ)</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($products)): ?>
                <tr><td colspan="8" style="text-align:center;">Chưa có sản phẩm nào.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['supplier_name'] ?? 'N/A') ?></td>
                    <td><?= number_format($p['price'], 0, ',', '.') ?> đ</td>
                    <td><?= $p['stock_quantity'] ?></td>
                    <td>
                        <?php if($p['is_active']): ?>
                            <span style="color: green; font-weight: bold;">Đang bán</span>
                        <?php else: ?>
                            <span style="color: red;">Ngừng bán</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>