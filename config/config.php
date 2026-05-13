<?php
// Đường dẫn gốc của web PHP
// Tự động nhận diện xem là HTTP hay HTTPS (VS Code Tunnel dùng HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? "https://" : "http://";

// Lấy tên miền hiện tại (có thể là localhost hoặc cái link loằng ngoằng của VS Code)
$domainName = $_SERVER['HTTP_HOST'];

define('BASE_URL', '/project4');
// Đường dẫn gốc của Python API
define('API_URL', 'http://127.0.0.1:8000/api'); 
?>