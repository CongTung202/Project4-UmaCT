<?php 
require_once 'includes/header.php'; 

// Bắt buộc đăng nhập
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user = $_SESSION['user'];
?>

<style>
    .profile-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 30px;
        margin-top: 20px;
    }

    .profile-sidebar {
        background: #fff;
        padding: 30px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        height: fit-content;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #f0f0f0;
        color: #ccc;
        font-size: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border: 3px solid #ff3333;
    }

    .profile-name { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 5px; }
    .profile-role { font-size: 13px; color: #888; margin-bottom: 20px; }

    .profile-menu a {
        display: block;
        padding: 12px 15px;
        color: #555;
        text-decoration: none;
        border-radius: 6px;
        text-align: left;
        margin-bottom: 5px;
        transition: 0.3s;
    }
    .profile-menu a:hover, .profile-menu a.active {
        background: #ffe0e0;
        color: #ff3333;
        font-weight: bold;
    }
    .profile-menu i { width: 25px; }

    .profile-main {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: 0.3s; }
    .form-control:focus { border-color: #ff3333; outline: none; }
    .form-control:disabled { background: #f5f5f5; cursor: not-allowed; color: #888; }

    .btn-save {
        background: #ff3333;
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-save:hover { background: #e60000; }

    @media (max-width: 768px) {
        .profile-container { grid-template-columns: 1fr; }
    }
</style>

<div style="width: 100%;">
    <h2 style="margin-bottom: 20px;"><i class="fas fa-user-cog"></i> Quản lý tài khoản</h2>

    <div class="profile-container">
        <!-- CỘT TRÁI: AVATAR & MENU -->
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="profile-role">Khách hàng thành viên</div>

            <div class="profile-menu">
                <a href="profile.php" class="active"><i class="fas fa-id-card"></i> Thông tin cá nhân</a>
                <a href="order_history.php"><i class="fas fa-box"></i> Đơn hàng của tôi</a>
                <a href="logout.php" style="color: #e74c3c; margin-top: 20px; border-top: 1px solid #eee; border-radius: 0;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <!-- CỘT PHẢI: FORM CHỈNH SỬA -->
        <div class="profile-main">
            <h3 style="margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Hồ sơ của tôi</h3>
            
            <form id="profileForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label>Họ và tên (Dùng để nhận hàng) <span style="color: red;">*</span></label>
                    <input type="text" id="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Nhập họ và tên đầy đủ...">
                </div>

                <div class="form-group">
                    <label>Số điện thoại <span style="color: red;">*</span></label>
                    <input type="tel" id="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại liên hệ...">
                </div>

                <div class="form-group">
                    <label>Địa chỉ mặc định</label>
                    <textarea id="address" class="form-control" rows="3" placeholder="Nhập địa chỉ nhận hàng chi tiết..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-save" id="btnSaveProfile">Lưu Thay Đổi</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Chặn tải lại trang
    
    const btn = document.getElementById('btnSaveProfile');
    const originalText = btn.innerText;
    
    // Validate cơ bản
    const fullName = document.getElementById('full_name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();

    if (!fullName || !phone) {
        showToast('Vui lòng điền đủ Họ tên và Số điện thoại!', 'error');
        return;
    }

    // Hiệu ứng loading
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    btn.disabled = true;

    // Gửi AJAX
    fetch('ajax_update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            full_name: fullName,
            phone: phone,
            address: address
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;

        if (data.status === 'success') {
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        showToast('Lỗi kết nối, vui lòng thử lại!', 'error');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>