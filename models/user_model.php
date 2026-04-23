<?php
// Lấy danh sách người dùng và danh sách quyền
function getAllUsersAndRoles() {
    $ch = curl_init(API_URL . '/users');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? ['users' => [], 'roles' => []];
}

// Cập nhật trạng thái tài khoản (ACTIVE / BANNED)
function updateUserStatus($id, $status) {
    $data = json_encode(['status' => $status]);
    $ch = curl_init(API_URL . '/users/' . $id . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật trạng thái');
}

// Cập nhật chức vụ (Phân quyền)
function updateUserRole($id, $role_id) {
    $data = json_encode(['role_id' => $role_id]);
    $ch = curl_init(API_URL . '/users/' . $id . '/role');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật quyền');
}
// Lấy chi tiết thông tin người dùng và lịch sử mua hàng
function getUserDetail($id) {
    $ch = curl_init(API_URL . '/users/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? null;
}
function registerUser($data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/register');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) return true;
    throw new Exception(json_decode($response, true)['detail'] ?? 'Lỗi đăng ký');
}

function loginUser($username, $password) {
    // Đóng gói dữ liệu thành chuỗi JSON
    $jsonData = json_encode([
        'username' => $username, 
        'password' => $password
    ]);
    
    $ch = curl_init(API_URL . '/login');
    
    // CÁC CẤU HÌNH BẮT BUỘC ĐỂ GỬI POST BẰNG CURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // Ép dùng phương thức POST (Sửa lỗi 405)
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Nhét dữ liệu vào body
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Khai báo Header để Python biết đây là dữ liệu JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Nếu Python trả về 200 OK -> Thành công
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return $result['data'];
    }
    
    // Nếu thất bại (Sai pass, khóa tài khoản...) -> Ném lỗi ra màn hình
    $errorData = json_decode($response, true);
    throw new Exception($errorData['detail'] ?? 'Lỗi đăng nhập từ máy chủ');
}
?>