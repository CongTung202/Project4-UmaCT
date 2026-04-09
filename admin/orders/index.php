<?php
require_once '../../admin/includes/header.php';
require_once '../../models/order_model.php';

$error = '';
$success = '';

// Bắt sự kiện Đổi trạng thái nhanh từ dropdown
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    try {
        updateOrderStatus($_POST['order_id'], $_POST['status']);
        $success = "Cập nhật trạng thái đơn hàng #{$_POST['order_id']} thành công!";
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Bắt thông báo từ file delete.php trả về
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success = "Đã xóa đơn hàng thành công!";
}

$orders = getAllOrders();
?>

<style>
    /* Nền đen mờ của Modal */
    .modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0; 
        width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);
    }
    /* Hộp nội dung Modal */
    .modal-content {
        background-color: #fff; margin: 15% auto; padding: 20px; 
        border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: black; }
    
    /* CSS cho dropdown trạng thái */
    .select-status { padding: 5px; border-radius: 4px; border: 1px solid #ccc; outline: none;}
</style>

<div style="margin: 20px;">
    <h2>Quản lý Đơn hàng</h2>

    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $error ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Trạng thái (Đổi nhanh)</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($orders)): ?>
                <tr><td colspan="6" style="text-align:center;">Chưa có đơn hàng nào.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= htmlspecialchars($o['full_name'] ?? $o['username']) ?></td>
                    <td style="color: red; font-weight: bold;"><?= number_format($o['total_price'], 0, ',', '.') ?> đ</td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    
                    <td>
                        <form action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="select-status" onchange="this.form.submit()">
                                <option value="PENDING" <?= $o['status'] == 'PENDING' ? 'selected' : '' ?>>Chờ xử lý</option>
                                <option value="PAID" <?= $o['status'] == 'PAID' ? 'selected' : '' ?>>Đã thanh toán</option>
                                <option value="SHIPPING" <?= $o['status'] == 'SHIPPING' ? 'selected' : '' ?>>Đang giao</option>
                                <option value="COMPLETED" <?= $o['status'] == 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                                <option value="CANCELLED" <?= $o['status'] == 'CANCELLED' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </form>
                    </td>
                    
                    <td>
                        <a href="detail.php?id=<?= $o['id'] ?>" class="btn btn-edit" style="background-color: #17a2b8;">Chi tiết</a>
                        <button class="btn btn-delete" onclick="openModal(<?= $o['id'] ?>)" style="cursor: pointer; border: none;">Xóa</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="margin-top: 0; color: #d9534f;">Cảnh báo xóa!</h3>
        <p>Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng <strong id="modalOrderId" style="font-size: 18px;"></strong> không?</p>
        <p style="color: #666; font-size: 14px;">Hành động này sẽ xóa toàn bộ chi tiết sản phẩm bên trong đơn hàng và không thể khôi phục.</p>
        
        <form action="delete.php" method="POST" style="text-align: right; margin-top: 20px;">
            <input type="hidden" name="id" id="hiddenOrderIdInput">
            <button type="button" class="btn" style="background-color: #6c757d; cursor: pointer; border: none; padding: 8px 15px;" onclick="closeModal()">Hủy bỏ</button>
            <button type="submit" class="btn btn-delete" style="cursor: pointer; border: none; padding: 8px 15px;">Vẫn Xóa</button>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("deleteModal");

    // Hàm mở modal và gán ID đơn hàng vào form
    function openModal(orderId) {
        document.getElementById("modalOrderId").innerText = "#" + orderId;
        document.getElementById("hiddenOrderIdInput").value = orderId;
        modal.style.display = "block";
    }

    // Hàm đóng modal
    function closeModal() {
        modal.style.display = "none";
    }

    // Click ra ngoài khoảng đen để đóng modal
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>