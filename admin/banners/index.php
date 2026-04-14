<?php
require_once '../../admin/includes/header.php';
require_once '../../models/banner_model.php';

$banners = getAllBanners();
?>

<div style="margin: 20px;">
    <h2>Quản lý Banner quảng cáo</h2>
    <a href="create.php" class="btn btn-add">+ Thêm Banner mới</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hình ảnh</th>
                <th>Vị trí</th>
                <th>Đường dẫn (Link)</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($banners)): ?>
                <tr><td colspan="5" style="text-align:center;">Chưa có banner nào.</td></tr>
            <?php else: ?>
                <?php foreach ($banners as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="Banner" style="max-width: 200px; height: auto; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    </td>
                    <td><strong><?= htmlspecialchars($b['position'] ?? 'Mặc định') ?></strong></td>
                    <td><a href="<?= htmlspecialchars($b['link'] ?? '#') ?>" target="_blank" style="color: #007bff;"><?= htmlspecialchars($b['link'] ?? 'Không có') ?></a></td>
                    <td>
                        <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $b['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>