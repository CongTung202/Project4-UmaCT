<?php
// Lấy danh sách sản phẩm
function getAllProducts() {
    $ch = curl_init(API_URL . '/products');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Đợi tối đa 15 giây
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return [];

    $result = json_decode($response, true);
    return $result['data'] ?? [];
}

// Thêm sản phẩm
function addProduct($data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/products');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("Lỗi kết nối tới Server Python: " . $err);
    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi thêm sản phẩm');
}

// Lấy chi tiết 1 sản phẩm
function getProductById($id) {
    $ch = curl_init(API_URL . '/products/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return null;

    $result = json_decode($response, true);
    return $result['data'] ?? null;
}

// Cập nhật sản phẩm
function updateProduct($id, $data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/products/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("Lỗi kết nối tới Server Python: " . $err);
    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật sản phẩm');
}

// Xóa sản phẩm
function deleteProduct($id) {
    $ch = curl_init(API_URL . '/products/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("Lỗi kết nối tới Server Python: " . $err);
    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi xóa sản phẩm');
}
function searchProducts($keyword) {
    // encode từ khóa để truyền qua URL an toàn (hỗ trợ dấu cách)
    $ch = curl_init(API_URL . '/products/search?q=' . urlencode($keyword));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['data'] ?? [];
}
?>