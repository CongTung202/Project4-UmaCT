<?php 
session_start();
require_once '../config/config.php';
require_once '../models/user_model.php';

// Nếu đã đăng nhập thì không cho vào trang đăng ký
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra mật khẩu nhập lại có khớp không (Validation ở PHP)
    if ($_POST['password'] !== $_POST['re_password']) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        try {
            $data = [
                'username'  => trim($_POST['username']),
                'password'  => $_POST['password'], // Gửi mật khẩu thô theo yêu cầu của bạn
                'full_name' => trim($_POST['full_name']),
                'email'     => trim($_POST['email'])
            ];

            if (registerUser($data)) {
                // Đăng ký thành công, chuyển hướng sang trang đăng nhập kèm thông báo
                header("Location: login.php?msg=registered");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - UmaCT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            position: relative;
            padding: 40px 0; /* Thêm padding cho màn hình nhỏ */
        }

        /* Nút quay về trang chủ */
        .btn-back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            text-decoration: none;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .btn-back-home:hover {
            color: #ff3333;
            transform: translateX(-3px);
        }

        .auth-wrapper {
            width: 100%;
            max-width: 450px;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .auth-logo {
            display: block;
            margin: 0 auto 30px auto;
            height: 50px;
            object-fit: contain;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #ff3333;
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: #ff3333;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 15px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .btn-auth:hover {
            background: #e60000;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .auth-footer a {
            color: #ff3333;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            color: #e74c3c;
            background: #fceae9;
        }
    </style>
</head>
<body>

    <a href="index.php" class="btn-back-home">
        <i class="fas fa-arrow-left"></i> Quay về trang chủ
    </a>

    <div class="auth-wrapper">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="UmaCT Logo" class="auth-logo" onerror="this.onerror=null; this.src='https://via.placeholder.com/200x50?text=UmaCT'">
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" class="form-input" required placeholder="Họ và tên..." value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-input" required placeholder="email@example.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Tên tài khoản</label>
                <input type="text" name="username" class="form-input" required placeholder="username..." value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-input" required placeholder="password...">
            </div>

            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="re_password" class="form-input" required placeholder="confirm password...">
            </div>

            <button type="submit" class="btn-auth">Tạo tài khoản</button>
        </form>
        
        <div class="auth-footer">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>
    </div>

</body>
</html>