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
?>