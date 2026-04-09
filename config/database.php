<?php
$host = 'localhost';
$dbname = 'umact_db';
$username = 'root'; // User mặc định của XAMPP/WAMP
$password = '123456';     // Mật khẩu mặc định thường để trống

try {
    // Kết nối với charset utf8mb4 để không bị lỗi font tiếng Việt
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Bật chế độ báo lỗi exception để dễ debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>