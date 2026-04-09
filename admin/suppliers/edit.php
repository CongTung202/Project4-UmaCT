<?php
require_once '../../admin/includes/header.php';
require_once '../../models/supplier_model.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID nhà cung cấp.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$supplier = getSupplierById($id);

if (!$supplier) {
    die("<div style='margin: 20px;'><h2>Lỗi: Nhà cung cấp không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'name' => trim($_POST['name']),
        'contact_info' => trim($_POST['contact_info']),
        'address' => trim($_POST['address'])
    ];

    if (empty($data['name'])) {
        $error = "Vui lòng nhập Tên nhà cung cấp!";
    } else {
        try {
            updateSupplier($id, $data);
            $success = "Cập nhật thông tin thành công!";
            // Cập nhật lại mảng hiển thị trên form
            $supplier = array_merge($supplier, $data);
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Sửa Nhà cung cấp</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 500px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tên nhà cung cấp:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($supplier['name']) ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Thông tin liên hệ (Email/Phone):</label>
            <input type="text" name="contact_info" class="form-control" value="<?= htmlspecialchars($supplier['contact_info'] ?? '') ?>" style="width: 100%; padding: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Địa chỉ:</label>
            <textarea name="address" class="form-control" rows="3" style="width: 100%; padding: 8px;"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-edit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu thay đổi</button>
    </form>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>