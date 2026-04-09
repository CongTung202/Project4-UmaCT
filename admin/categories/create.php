<?php
// 1. Nhúng các file cần thiết
require_once '../../admin/includes/header.php';
require_once '../../models/category_model.php';

$error = '';

// 2. Xử lý khi form được submit (Người dùng nhấn Thêm mới)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    if (empty($name) || empty($slug)) {
        $error = "Vui lòng nhập đầy đủ Tên danh mục và Slug!";
    } else {
        try {
            // Gọi hàm thêm mới (gửi request POST sang Python API)
            addCategory($name, $slug);
            
            // Nếu thêm thành công, chuyển hướng thẳng về trang danh sách
            header("Location: index.php");
            exit;
            
        } catch (Exception $e) {
            // Bắt lỗi từ API (ví dụ: nhập trùng Slug đã tồn tại)
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Thêm Danh mục mới</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 400px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Tên danh mục:</label>
            <input type="text" name="name" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;" placeholder="VD: Phụ kiện Anime">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Slug (Đường dẫn tĩnh):</label>
            <input type="text" name="slug" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;" placeholder="VD: phu-kien-anime">
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 4px;">Lưu danh mục</button>
    </form>
</div>

</body>
</html>