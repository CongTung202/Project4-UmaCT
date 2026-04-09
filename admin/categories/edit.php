<?php
require_once '../../admin/includes/header.php';
require_once '../../models/category_model.php';

$error = '';
$success = '';

// Kiểm tra xem có truyền ID lên không
if (!isset($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID danh mục.");
}

$id = $_GET['id'];
$category = getCategoryById($id);

if (!$category) {
    die("Lỗi: Danh mục không tồn tại.");
}

// Xử lý khi người dùng bấm Lưu (Submit Form)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    if (empty($name) || empty($slug)) {
        $error = "Vui lòng nhập đầy đủ Tên và Slug!";
    } else {
        try {
            updateCategory($id, $name, $slug);
            // Sửa thành công thì tải lại dữ liệu mới nhất
            $category = getCategoryById($id); 
            $success = "Cập nhật danh mục thành công!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

    <h2>Sửa Danh mục</h2>
    <a href="index.php" class="btn" style="background: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom:10px;"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: green; padding: 10px; border: 1px solid green; margin-bottom:10px;"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Tên danh mục:</label><br>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Slug:</label><br>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug']) ?>" required>
        </div>
        <button type="submit" class="btn btn-edit">Lưu thay đổi</button>
    </form>

<?php require_once '../../admin/includes/footer.php'; ?>