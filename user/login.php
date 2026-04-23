<?php 
// Bắt đầu session ở đây vì chúng ta không gọi header.php nữa
session_start();
require_once '../config/config.php';
require_once '../models/user_model.php';

// Nếu đã đăng nhập rồi thì đá về trang chủ, không cho vào trang login nữa
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $user = loginUser($_POST['username'], $_POST['password']);
        $_SESSION['user'] = $user; // Lưu thông tin vào Session
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - UmaCT</title>
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
        }

        /* Nút quay về trang chủ ở góc trái */
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
        }

        .btn-back-home:hover {
            color: #ff3333;
            box-shadow: 0 6px 15px rgba(255, 51, 51, 0.15);
            transform: translateX(-3px);
        }

        /* Hộp Form Đăng nhập */
        .auth-wrapper {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .auth-logo {
            display: block;
            margin: 0 auto 30px auto;
            height: 45px;
            object-fit: contain;
        }

        .form-group {
            margin-bottom: 20px;
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
    </style>
</head>
<body>

    <a href="index.php" class="btn-back-home">
        <i class="fas fa-arrow-left"></i> Quay về trang chủ
    </a>

    <div class="auth-wrapper">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="UmaCT Logo" class="auth-logo" onerror="this.onerror=null; this.src='https://via.placeholder.com/200x50?text=UmaCT'">
        
        <?php if($error): ?>
            <div style="color: #e74c3c; background: #fceae9; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Tên tài khoản</label>
                <input type="text" name="username" class="form-input" required placeholder="username...">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-input" required placeholder="password...">
            </div>
            <button type="submit" class="btn-auth">Đăng nhập</button>
        </form>
        
        <div class="auth-footer">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>

</body>
</html>