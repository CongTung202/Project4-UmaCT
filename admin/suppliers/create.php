<?php
require_once '../../admin/includes/header.php';
require_once '../../models/supplier_model.php';

$error = '';

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
            addSupplier($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div style="margin: 20px;">
    <h2>Thêm Nhà cung cấp</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 500px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tên nhà cung cấp:</label>
            <input type="text" name="name" class="form-control" required style="width: 100%; padding: 8px;" placeholder="VD: Kotobukiya">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Thông tin liên hệ (Email/Phone):</label>
            <input type="text" name="contact_info" class="form-control" style="width: 100%; padding: 8px;" placeholder="VD: support@kotobukiya.co.jp">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Địa chỉ:</label>
            <textarea name="address" class="form-control" rows="3" style="width: 100%; padding: 8px;" placeholder="VD: Tokyo, Japan"></textarea>
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Lưu lại</button>
    </form>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>