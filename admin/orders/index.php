<?php
require_once '../../admin/includes/header.php';
require_once '../../models/order_model.php';

$error = '';
$success = '';

// Bắt sự kiện Đổi trạng thái từ Modal xác nhận
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

// 1. LẤY DỮ LIỆU BAN ĐẦU
$all_orders = getAllOrders();

// 2. NHẬN THAM SỐ TÌM KIẾM TỪ URL (GET)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10; // Số đơn hàng hiển thị trên 1 trang

// 3. XỬ LÝ LỌC ĐƠN HÀNG (FILTER)
$filtered_orders = [];
foreach ($all_orders as $o) {
    // Tìm kiếm theo ID đơn hàng HOẶC Tên khách hàng
    $customer_name = $o['full_name'] ?? $o['username'];
    $match_keyword = empty($keyword) || 
                     strpos((string)$o['id'], $keyword) !== false || 
                     mb_stripos($customer_name, $keyword, 0, 'UTF-8') !== false;
    
    // Lọc theo trạng thái
    $match_status = empty($status_filter) || $o['status'] == $status_filter;
    
    if ($match_keyword && $match_status) {
        $filtered_orders[] = $o;
    }
}

// 4. XỬ LÝ PHÂN TRANG (PAGINATION)
$total_items = count($filtered_orders);
$total_pages = ceil($total_items / $limit);
if ($total_pages > 0 && $page > $total_pages) $page = $total_pages; // Tránh trang vượt quá giới hạn
$offset = ($page - 1) * $limit;

// Cắt mảng để lấy đúng số đơn hàng của trang hiện tại
$orders = array_slice($filtered_orders, $offset, $limit);
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
    .select-status { padding: 5px; border-radius: 4px; border: 1px solid #ccc; outline: none; cursor: pointer;}

    /* CSS Phân trang & Lọc */
    .pagination { display: flex; gap: 8px; justify-content: center; margin-top: 25px; }
    .page-link { 
        padding: 8px 15px; background: #fff; border: 1px solid #ddd; 
        color: #333; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;
    }
    .page-link:hover { background: #f4f6f9; border-color: #ff3333; color: #ff3333; }
    .page-link.active { background: #ff3333; color: #fff; border-color: #ff3333; }
    
    .filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .filter-form { display: flex; gap: 10px; align-items: center; width: 100%; }
</style>

<div class="main-content">
    <h2>Quản lý Đơn hàng</h2>

    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #28a745;"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #dc3545;"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
    <div style="color: #155724; background-color: #d4edda; padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold;">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="keyword" class="form-control" placeholder="Mã ĐH hoặc Tên khách hàng..." value="<?= htmlspecialchars($keyword) ?>" style="width: 250px; padding: 8px;">
            
            <select name="status_filter" class="form-control" style="width: 200px; padding: 8px;">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="PENDING" <?= $status_filter == 'PENDING' ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="PAID" <?= $status_filter == 'PAID' ? 'selected' : '' ?>>Đã thanh toán</option>
                <option value="SHIPPING" <?= $status_filter == 'SHIPPING' ? 'selected' : '' ?>>Đang giao</option>
                <option value="COMPLETED" <?= $status_filter == 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="CANCELLED" <?= $status_filter == 'CANCELLED' ? 'selected' : '' ?>>Đã hủy</option>
            </select>
            
            <button type="submit" class="btn btn-edit" style="margin: 0;"><i class="fas fa-search"></i> Lọc đơn hàng</button>
            <a href="index.php" class="btn" style="background: #6c757d; margin: 0;"><i class="fas fa-sync-alt"></i> Hủy</a>
        </form>
    </div>

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
                <tr><td colspan="6" style="text-align:center; padding: 30px; color: #888;">Không tìm thấy đơn hàng nào phù hợp!</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong style="color: #ff3333;">#<?= $o['id'] ?></strong></td>
                    <td><?= htmlspecialchars($o['full_name'] ?? $o['username']) ?></td>
                    <td style="color: #333; font-weight: bold;"><?= number_format($o['total_price'], 0, ',', '.') ?> đ</td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    
                    <td>
                        <form id="status-form-<?= $o['id'] ?>" action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="select-status" 
                                    onfocus="this.setAttribute('data-old-value', this.value);" 
                                    onchange="openStatusModal(this, <?= $o['id'] ?>)">
                                <option value="PENDING" <?= $o['status'] == 'PENDING' ? 'selected' : '' ?>>Chờ xử lý</option>
                                <option value="PAID" <?= $o['status'] == 'PAID' ? 'selected' : '' ?>>Đã thanh toán</option>
                                <option value="SHIPPING" <?= $o['status'] == 'SHIPPING' ? 'selected' : '' ?>>Đang giao</option>
                                <option value="COMPLETED" <?= $o['status'] == 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                                <option value="CANCELLED" <?= $o['status'] == 'CANCELLED' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </form>
                    </td>
                    
                    <td>
                        <a href="detail.php?id=<?= $o['id'] ?>" class="btn btn-edit" style="background-color: #17a2b8;" title="Xem chi tiết"><i class="fas fa-eye"></i> Chi tiết</a>
                        <a href="delete.php?id=<?= $o['id'] ?>" 
                            class="btn btn-delete" 
                            style="background-color: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px;"
                            onclick="return confirm('CẢNH BÁO: Bác có chắc chắn muốn xóa vĩnh viễn đơn hàng #<?= $o['id'] ?> không?\n\nHành động này KHÔNG THỂ khôi phục!');">
                            <i class="fas fa-trash"></i> Xóa đơn
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>&status_filter=<?= urlencode($status_filter) ?>" class="page-link">&laquo;</a>
            <?php endif; ?>

            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&status_filter=<?= urlencode($status_filter) ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>&status_filter=<?= urlencode($status_filter) ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 15px; color: #888; font-size: 13px;">
        Hiển thị trang <?= $page ?> / <?= $total_pages > 0 ? $total_pages : 1 ?> (Tổng cộng <?= $total_items ?> đơn hàng)
    </div>
</div>

<div id="statusModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeStatusModal()">&times;</span>
        <h3 style="margin-top: 0; color: #007bff;"><i class="fas fa-exchange-alt"></i> Xác nhận đổi trạng thái</h3>
        <p>Bạn có chắc chắn muốn đổi trạng thái đơn hàng <strong id="statusModalOrderId" style="font-size: 18px;"></strong> thành <strong id="statusModalNewStatus" style="color: #ff3333;"></strong>?</p>
        
        <div style="text-align: right; margin-top: 20px;">
            <button type="button" class="btn" style="background-color: #6c757d; cursor: pointer; border: none; padding: 8px 15px;" onclick="closeStatusModal()">Hủy bỏ</button>
            <button type="button" class="btn btn-add" style="cursor: pointer; border: none; padding: 8px 15px; margin-bottom: 0;" onclick="confirmSubmitStatus()">Đồng ý</button>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h3 style="margin-top: 0; color: #d9534f;"><i class="fas fa-exclamation-triangle"></i> Cảnh báo xóa!</h3>
        <p>Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng <strong id="modalOrderId" style="font-size: 18px;"></strong> không?</p>
        <p style="color: #666; font-size: 14px;">Hành động này sẽ xóa toàn bộ chi tiết sản phẩm bên trong đơn hàng và không thể khôi phục.</p>
        
        <form action="delete.php" method="POST" style="text-align: right; margin-top: 20px;">
            <input type="hidden" name="id" id="hiddenOrderIdInput">
            <button type="button" class="btn" style="background-color: #6c757d; cursor: pointer; border: none; padding: 8px 15px;" onclick="closeDeleteModal()">Hủy bỏ</button>
            <button type="submit" class="btn btn-delete" style="cursor: pointer; border: none; padding: 8px 15px;">Vẫn Xóa</button>
        </form>
    </div>
</div>

<script>
    // --- XỬ LÝ MODAL XÓA ĐƠN HÀNG ---
    var deleteModal = document.getElementById("deleteModal");

    function openDeleteModal(orderId) {
        document.getElementById("modalOrderId").innerText = "#" + orderId;
        document.getElementById("hiddenOrderIdInput").value = orderId;
        deleteModal.style.display = "block";
    }

    function closeDeleteModal() {
        deleteModal.style.display = "none";
    }

    // --- XỬ LÝ MODAL ĐỔI TRẠNG THÁI ---
    var statusModal = document.getElementById("statusModal");
    var currentSelectElement = null; // Lưu lại thẻ select đang thao tác
    var currentOrderId = null;       // Lưu ID đơn hàng

    function openStatusModal(selectElement, orderId) {
        currentSelectElement = selectElement;
        currentOrderId = orderId;
        
        // Lấy chữ (text) của tùy chọn vừa được chọn (VD: "Đang giao")
        var newStatusText = selectElement.options[selectElement.selectedIndex].text;
        
        document.getElementById("statusModalOrderId").innerText = "#" + orderId;
        document.getElementById("statusModalNewStatus").innerText = newStatusText;
        
        statusModal.style.display = "block";
    }

    function closeStatusModal() {
        // Nếu người dùng bấm Hủy, ta phải trả thẻ Select về giá trị cũ
        if (currentSelectElement) {
            var oldValue = currentSelectElement.getAttribute('data-old-value');
            currentSelectElement.value = oldValue;
        }
        statusModal.style.display = "none";
    }

    function confirmSubmitStatus() {
        // Nếu đồng ý, ta tìm form chứa thẻ select đó và gửi lệnh submit đi
        if (currentOrderId) {
            document.getElementById("status-form-" + currentOrderId).submit();
        }
    }

    // --- CLICK RA NGOÀI VÙNG ĐEN ĐỂ ĐÓNG ---
    window.onclick = function(event) {
        if (event.target == deleteModal) {
            closeDeleteModal();
        }
        if (event.target == statusModal) {
            closeStatusModal(); // Gọi hàm này để nó hoàn tác cả dropdown
        }
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>