<?php
require_once '../../admin/includes/header.php';
require_once '../../models/category_model.php';
require_once '../../models/product_model.php';

// Lấy danh sách danh mục để đổ vào dropdown
$categories = getAllCategories();

// Tạm thời hardcode supplier nếu bạn chưa làm bảng suppliers.
// Nếu làm rồi, hãy gọi $suppliers = getAllSuppliers();
$suppliers = [
    ['id' => 1, 'name' => 'Good Smile Company'],
    ['id' => 2, 'name' => 'Cygames Store']
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Thu thập dữ liệu từ form
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
            addProduct($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Thêm Sản phẩm mới</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 600px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tên sản phẩm:</label>
            <input type="text" name="name" class="form-control" required style="width: 100%; padding: 8px;" placeholder="VD: Nendoroid Special Week">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Danh mục:</label>
                <select name="category_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Nhà cung cấp:</label>
                <select name="supplier_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Giá bán (VNĐ):</label>
                <input type="number" name="price" class="form-control" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="flex: 1;">
                <label style="font-weight: bold;">Số lượng tồn kho:</label>
                <input type="number" name="stock_quantity" class="form-control" required style="width: 100%; padding: 8px;">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Trạng thái:</label>
            <select name="is_active" class="form-control" style="width: 100%; padding: 8px;">
                <option value="1">Đang bán (Hiển thị)</option>
                <option value="0">Ngừng bán (Ẩn)</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Mô tả chi tiết:</label>
            <textarea name="description" class="form-control" rows="5" style="width: 100%; padding: 8px;"></textarea>
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu sản phẩm</button>
    </form>
</div>

</body>
</html>