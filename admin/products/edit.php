<?php
require_once '../../admin/includes/header.php';
require_once '../../models/category_model.php';
require_once '../../models/product_model.php';
require_once '../../models/supplier_model.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID sản phẩm.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$product = getProductById($id);

if (!$product) {
    die("<div style='margin: 20px;'><h2>Lỗi: Sản phẩm không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

// Lấy danh sách để đổ vào dropdown
$categories = getAllCategories();
$suppliers = getAllSuppliers();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'name' => trim($_POST['name']),
        'category_id' => (int)$_POST['category_id'],
        'supplier_id' => (int)$_POST['supplier_id'],
        'price' => (float)$_POST['price'],
        'stock_quantity' => (int)$_POST['stock_quantity'],
        'description' => trim($_POST['description']),
        'is_active' => isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true // <-- THÊM DÒNG NÀY
    ];

    if (empty($data['name']) || empty($data['price'])) {
        $error = "Vui lòng nhập Tên sản phẩm và Giá!";
    } else {
        try {
            updateProduct($id, $data);
            $success = "Cập nhật sản phẩm thành công!";
            // Cập nhật lại mảng hiển thị
            $product = array_merge($product, $data);
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Sửa Sản phẩm: <?= htmlspecialchars($product['name']) ?></h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 600px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tên sản phẩm:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Danh mục:</label>
                <select name="category_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Nhà cung cấp:</label>
                <select name="supplier_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>" <?= ($product['supplier_id'] == $sup['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sup['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Giá bán (VNĐ):</label>
                <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Số lượng tồn kho:</label>
                <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>" required style="width: 100%; padding: 8px;">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Trạng thái:</label>
            <select name="is_active" class="form-control" style="width: 100%; padding: 8px;">
                <option value="1" <?= ($product['is_active'] == 1) ? 'selected' : '' ?>>Đang bán (Hiển thị)</option>
                <option value="0" <?= ($product['is_active'] == 0) ? 'selected' : '' ?>>Ngừng bán (Ẩn)</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Mô tả chi tiết:</label>
            <textarea name="description" class="form-control" rows="5" style="width: 100%; padding: 8px;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-edit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu thay đổi</button>
    </form>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>