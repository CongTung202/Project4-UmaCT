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
                <th>Phân quyền</th>
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
                        <form action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role_id" style="padding: 5px; border-radius: 4px;" onchange="this.form.submit()" <?= $u['id'] == 1 ? 'disabled' : '' ?>>
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
                            <form action="" method="POST" style="margin: 0;">
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

<?php require_once '../../admin/includes/footer.php'; ?>