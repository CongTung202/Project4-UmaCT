<?php
// 1. Nhúng giao diện header
require_once '../../admin/includes/header.php';
// 2. Nhúng file model (nơi chứa các hàm gọi API Python)
require_once '../../models/category_model.php';

// 3. Gọi hàm để lấy danh sách từ API gán vào biến $categories
// (Nếu dùng PDO thuần thì truyền $pdo vào, nếu gọi API bằng cURL như bước trước thì không cần truyền)
$categories = getAllCategories(); 
?>

    <h2>Danh sách Danh mục sản phẩm</h2>
    <a href="create.php" class="btn btn-add">+ Thêm danh mục mới</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Slug (Đường dẫn tĩnh)</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($categories)): ?>
                <tr><td colspan="4" style="text-align:center;">Chưa có danh mục nào hoặc API đang không trả về dữ liệu.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['slug']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $cat['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $cat['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php require_once '../../admin/includes/footer.php'; ?>