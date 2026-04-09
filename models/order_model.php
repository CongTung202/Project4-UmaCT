<?php
// Lấy danh sách đơn hàng
function getAllOrders() {
    $ch = curl_init(API_URL . '/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? [];
}

// Lấy chi tiết đơn hàng (gồm thông tin và danh sách món hàng)
function getOrderDetail($id) {
    $ch = curl_init(API_URL . '/orders/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? null; // Trả về mảng chứa 'order_info' và 'items'
}

// Cập nhật trạng thái đơn hàng
function updateOrderStatus($id, $status) {
    $data = json_encode(['status' => $status]);
    $ch = curl_init(API_URL . '/orders/' . $id . '/status');
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
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật trạng thái đơn hàng');
}
// Xóa đơn hàng
function deleteOrder($id) {
    $ch = curl_init(API_URL . '/orders/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi xóa đơn hàng');
}
?>