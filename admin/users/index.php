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

// Lấy dữ liệu (gồm mảng users và mảng roles)
$data = getAllUsersAndRoles();
$users = $data['users'];
$roles = $data['roles'];
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
</style>

<div style="margin: 20px;">
    <h2>Quản lý Người dùng & Phân quyền</h2>

    <?php if ($success): ?>
        <div style="color: #155724; background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color: #721c24; background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?= $error ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tài khoản</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Phân quyền (Đổi nhanh)</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($users)): ?>
                <tr><td colspan="7" style="text-align:center;">Không có dữ liệu người dùng.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= htmlspecialchars($u['full_name'] ?? 'Chưa cập nhật') ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? 'Không có') ?></td>
                    
                    <td>
                        <form id="role-form-<?= $u['id'] ?>" action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role_id" style="padding: 5px; border-radius: 4px;" 
                                    onfocus="this.setAttribute('data-old-value', this.value);" 
                                    onchange="openRoleModal(this, <?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')" 
                                    <?= $u['id'] == 1 ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $u['role_id'] == $r['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td>
                        <?php if($u['status'] == 'ACTIVE'): ?>
                            <span style="color: green; font-weight: bold;">Hoạt động</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">Bị khóa</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="detail.php?id=<?= $u['id'] ?>" class="btn" style="background-color: #17a2b8; padding: 6px 12px; font-size: 13px;">Chi tiết</a>
                        <?php if ($u['id'] != 1): ?>
                            <form action="" method="POST" style="margin: 0; display: inline-block;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                
                                <?php if($u['status'] == 'ACTIVE'): ?>
                                    <input type="hidden" name="status" value="BANNED">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn KHÓA tài khoản này?');" style="cursor: pointer; border: none; padding: 6px 12px;">Khóa tài khoản</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="ACTIVE">
                                    <button type="submit" class="btn btn-add" onclick="return confirm('Bạn muốn MỞ KHÓA tài khoản này?');" style="cursor: pointer; border: none; padding: 6px 12px; background-color: #28a745;">Mở khóa</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <span style="color: gray; font-size: 12px;">Bảo vệ hệ thống</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="roleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRoleModal()">&times;</span>
        <h3 style="margin-top: 0; color: #ffc107;">Xác nhận phân quyền</h3>
        <p>Bạn có chắc chắn muốn đổi quyền của tài khoản <strong id="roleModalUsername" style="font-size: 16px;"></strong> thành <strong id="roleModalNewRole" style="color: red;"></strong>?</p>
        
        <div style="text-align: right; margin-top: 20px;">
            <button type="button" class="btn" style="background-color: #6c757d; cursor: pointer; border: none; padding: 8px 15px;" onclick="closeRoleModal()">Hủy bỏ</button>
            <button type="button" class="btn btn-add" style="cursor: pointer; border: none; padding: 8px 15px; background-color: #ffc107; color: black;" onclick="confirmSubmitRole()">Đồng ý</button>
        </div>
    </div>
</div>

<script>
    // --- XỬ LÝ MODAL ĐỔI PHÂN QUYỀN ---
    var roleModal = document.getElementById("roleModal");
    var currentRoleSelect = null; // Lưu lại thẻ select đang thao tác
    var currentUserId = null;     // Lưu ID user

    function openRoleModal(selectElement, userId, username) {
        currentRoleSelect = selectElement;
        currentUserId = userId;
        
        // Lấy chữ (text) của tùy chọn vừa được chọn (VD: "ROLE_ADMIN")
        var newRoleText = selectElement.options[selectElement.selectedIndex].text;
        
        document.getElementById("roleModalUsername").innerText = username;
        document.getElementById("roleModalNewRole").innerText = newRoleText;
        
        roleModal.style.display = "block";
    }

    function closeRoleModal() {
        // Nếu người dùng bấm Hủy, ta phải trả thẻ Select về giá trị cũ
        if (currentRoleSelect) {
            var oldValue = currentRoleSelect.getAttribute('data-old-value');
            currentRoleSelect.value = oldValue;
        }
        roleModal.style.display = "none";
    }

    function confirmSubmitRole() {
        // Nếu đồng ý, ta tìm form chứa thẻ select đó và gửi lệnh submit đi
        if (currentUserId) {
            document.getElementById("role-form-" + currentUserId).submit();
        }
    }

    // --- CLICK RA NGOÀI VÙNG ĐEN ĐỂ ĐÓNG ---
    window.onclick = function(event) {
        if (event.target == roleModal) {
            closeRoleModal();
        }
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>