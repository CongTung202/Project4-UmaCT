<?php
require_once '../../admin/includes/header.php';
require_once '../../models/user_model.php';

$error = '';
$success = '';

// Bắt sự kiện thao tác nhanh (Đổi trạng thái hoặc Đổi quyền)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    
    try {
        if ($_POST['action'] == 'update_status') {
            updateUserStatus($user_id, $_POST['status']);
            $status_text = $_POST['status'] == 'BANNED' ? 'Khóa' : 'Mở khóa';
            $success = "Đã $status_text tài khoản #$user_id thành công!";
        } 
        elseif ($_POST['action'] == 'update_role') {
            updateUserRole($user_id, (int)$_POST['role_id']);
            $success = "Đã phân quyền lại cho tài khoản #$user_id thành công!";
        }
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// 1. LẤY DỮ LIỆU BAN ĐẦU
$data = getAllUsersAndRoles();
$all_users = $data['users'];
$roles = $data['roles'];

// 2. NHẬN THAM SỐ TÌM KIẾM TỪ URL (GET)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10; // Số người dùng trên 1 trang

// 3. XỬ LÝ LỌC NGƯỜI DÚNG (FILTER)
$filtered_users = [];
foreach ($all_users as $u) {
    $full_name = $u['full_name'] ?? '';
    
    // Tìm theo Tên tài khoản, Họ tên hoặc Email
    $match_keyword = empty($keyword) || 
                     mb_stripos($u['username'], $keyword, 0, 'UTF-8') !== false || 
                     mb_stripos($full_name, $keyword, 0, 'UTF-8') !== false ||
                     mb_stripos($u['email'] ?? '', $keyword, 0, 'UTF-8') !== false;
                     
    $match_role = empty($role_filter) || $u['role_id'] == $role_filter;
    $match_status = empty($status_filter) || $u['status'] == $status_filter;
    
    if ($match_keyword && $match_role && $match_status) {
        $filtered_users[] = $u;
    }
}

// 4. XỬ LÝ PHÂN TRANG (PAGINATION)
$total_items = count($filtered_users);
$total_pages = ceil($total_items / $limit);
if ($total_pages > 0 && $page > $total_pages) $page = $total_pages; 
$offset = ($page - 1) * $limit;

// Cắt mảng lấy dữ liệu trang hiện tại
$users = array_slice($filtered_users, $offset, $limit);
?>

<style>
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-top: 4px solid #ffc107; }
    .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close:hover { color: #333; }
    
    .pagination { display: flex; gap: 8px; justify-content: center; margin-top: 25px; }
    .page-link { padding: 8px 15px; background: #fff; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s; }
    .page-link:hover { background: #f4f6f9; border-color: #ff3333; color: #ff3333; }
    .page-link.active { background: #ff3333; color: #fff; border-color: #ff3333; }
    
    .filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .filter-form { display: flex; gap: 10px; align-items: center; width: 100%; }
</style>

<div class="main-content">
    <h2><i class="fas fa-users" style="color: #ff3333;"></i> Quản lý Người dùng</h2>

    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 12px; margin-bottom: 20px; border-radius: 6px; border-left: 4px solid #28a745; font-weight: bold;"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 12px; margin-bottom: 20px; border-radius: 6px; border-left: 4px solid #dc3545; font-weight: bold;"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm Username, Họ tên, Email..." value="<?= htmlspecialchars($keyword) ?>" style="width: 250px; padding: 8px;">
            
            <select name="role_filter" class="form-control" style="width: 180px; padding: 8px;">
                <option value="">-- Tất cả Quyền --</option>
                <?php foreach($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $role_filter == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status_filter" class="form-control" style="width: 180px; padding: 8px;">
                <option value="">-- Tất cả Trạng thái --</option>
                <option value="ACTIVE" <?= $status_filter == 'ACTIVE' ? 'selected' : '' ?>>Hoạt động</option>
                <option value="BANNED" <?= $status_filter == 'BANNED' ? 'selected' : '' ?>>Bị khóa</option>
            </select>
            
            <button type="submit" class="btn btn-edit" style="margin: 0;"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn" style="background: #6c757d; margin: 0;"><i class="fas fa-sync-alt"></i> Hủy</a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tài khoản</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Phân quyền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($users)): ?>
                <tr><td colspan="7" style="text-align:center; padding: 30px; color: #888;">Không tìm thấy người dùng nào!</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                        <strong style="color: #ff3333;"><?= htmlspecialchars($u['username']) ?></strong>
                        <?php if($u['id'] == 1) echo '<i class="fas fa-crown" style="color: #ffc107; margin-left: 5px;" title="Super Admin"></i>'; ?>
                    </td>
                    <td><?= htmlspecialchars($u['full_name'] ?? '---') ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '---') ?></td>
                    
                    <td>
                        <form id="role-form-<?= $u['id'] ?>" action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role_id" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd; outline: none; background: <?= $u['id'] == 1 ? '#f5f5f5' : '#fff' ?>;" 
                                    onfocus="this.setAttribute('data-old-value', this.value);" 
                                    onchange="openRoleModal(this, <?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')" 
                                    <?= $u['id'] == 1 ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $u['role_id'] == $r['id'] ? 'selected' : '' ?>>
                                        <?= str_replace('ROLE_', '', $r['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td>
                        <?php if($u['status'] == 'ACTIVE'): ?>
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;"><i class="fas fa-check"></i> Mở</span>
                        <?php else: ?>
                            <span style="background: #ffebee; color: #c62828; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;"><i class="fas fa-lock"></i> Khóa</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="detail.php?id=<?= $u['id'] ?>" class="btn btn-edit" title="Chi tiết"><i class="fas fa-eye"></i></a>
                        
                        <?php if ($u['id'] != 1): ?>
                            <form action="" method="POST" style="margin: 0; display: inline-block;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                
                                <?php if($u['status'] == 'ACTIVE'): ?>
                                    <input type="hidden" name="status" value="BANNED">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('Khóa tài khoản này? Khách sẽ không thể đăng nhập!');" title="Khóa tài khoản"><i class="fas fa-user-lock"></i></button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="ACTIVE">
                                    <button type="submit" class="btn btn-add" style="margin:0; background-color: #28a745;" onclick="return confirm('Mở khóa tài khoản này?');" title="Mở khóa"><i class="fas fa-user-check"></i></button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>&role_filter=<?= urlencode($role_filter) ?>&status_filter=<?= urlencode($status_filter) ?>" class="page-link">&laquo;</a>
            <?php endif; ?>

            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&role_filter=<?= urlencode($role_filter) ?>&status_filter=<?= urlencode($status_filter) ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>&role_filter=<?= urlencode($role_filter) ?>&status_filter=<?= urlencode($status_filter) ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 15px; color: #888; font-size: 13px;">
        Hiển thị trang <?= $page ?> / <?= $total_pages > 0 ? $total_pages : 1 ?> (Tổng cộng <?= $total_items ?> tài khoản)
    </div>
</div>

<div id="roleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRoleModal()">&times;</span>
        <h3 style="margin-top: 0; color: #d35400;"><i class="fas fa-user-shield"></i> Xác nhận phân quyền</h3>
        <p>Bạn có chắc chắn muốn cấp quyền <strong id="roleModalNewRole" style="color: #ff3333; font-size: 18px;"></strong> cho tài khoản <strong id="roleModalUsername"></strong>?</p>
        <p style="font-size: 13px; color: #666; background: #fff3f3; padding: 10px; border-radius: 4px;">Lưu ý: Việc thay đổi quyền sẽ lập tức ảnh hưởng đến khả năng truy cập của người dùng này.</p>
        
        <div style="text-align: right; margin-top: 20px;">
            <button type="button" class="btn" style="background-color: #6c757d; cursor: pointer; border: none;" onclick="closeRoleModal()">Hủy bỏ</button>
            <button type="button" class="btn btn-add" style="margin: 0; cursor: pointer; border: none; background-color: #d35400;" onclick="confirmSubmitRole()">Đồng ý cấp quyền</button>
        </div>
    </div>
</div>

<script>
    var roleModal = document.getElementById("roleModal");
    var currentRoleSelect = null; 
    var currentUserId = null;     

    function openRoleModal(selectElement, userId, username) {
        currentRoleSelect = selectElement;
        currentUserId = userId;
        
        var newRoleText = selectElement.options[selectElement.selectedIndex].text;
        
        document.getElementById("roleModalUsername").innerText = username;
        document.getElementById("roleModalNewRole").innerText = newRoleText.trim();
        
        roleModal.style.display = "block";
    }

    function closeRoleModal() {
        if (currentRoleSelect) {
            var oldValue = currentRoleSelect.getAttribute('data-old-value');
            currentRoleSelect.value = oldValue;
        }
        roleModal.style.display = "none";
    }

    function confirmSubmitRole() {
        if (currentUserId) {
            document.getElementById("role-form-" + currentUserId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target == roleModal) closeRoleModal();
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>