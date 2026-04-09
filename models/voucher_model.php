<?php
// Gọi API lấy danh sách mã giảm giá
function getAllVouchers() {
    $ch = curl_init(API_URL . '/vouchers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? [];
}

// Gọi API thêm mã giảm giá mới
function addVoucher($data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/vouchers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi thêm mã giảm giá');
}
// Lấy chi tiết 1 mã giảm giá
function getVoucherById($id) {
    $ch = curl_init(API_URL . '/vouchers/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? null;
}

// Cập nhật mã giảm giá
function updateVoucher($id, $data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/vouchers/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật mã giảm giá');
}

// Xóa mã giảm giá
function deleteVoucher($id) {
    $ch = curl_init(API_URL . '/vouchers/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi xóa mã giảm giá');
}
?>