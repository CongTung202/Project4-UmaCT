<?php
// PHP làm client gọi sang Python API (cổng 8000)
define('API_URL', 'http://127.0.0.1:8000/api');

function getAllCategories() {
    $ch = curl_init(API_URL . '/categories');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Tắt kiểm tra SSL nếu dùng localhost để tránh lỗi
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    
    // Nếu có lỗi kết nối (ví dụ: Python tắt, sai URL)
    if(curl_errno($ch)){
        die('<b>Lỗi kết nối từ PHP sang Python:</b> ' . curl_error($ch));
    }
    curl_close($ch);

    // In thử cục dữ liệu thô ra màn hình để kiểm tra
    // var_dump($response); 

    $result = json_decode($response, true);
    return $result['data'] ?? [];
}

function addCategory($name, $slug) {
    $data = json_encode(['name' => $name, 'slug' => $slug]);

    // Dùng cURL cho request POST
    $ch = curl_init(API_URL . '/categories');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return true;
    } else {
        $error = json_decode($response, true);
        throw new Exception($error['detail'] ?? 'Lỗi không xác định từ API');
    }
}
// Lấy chi tiết 1 danh mục
function getCategoryById($id) {
    $ch = curl_init(API_URL . '/categories/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['data'] ?? null;
}

// Cập nhật danh mục
function updateCategory($id, $name, $slug) {
    $data = json_encode(['name' => $name, 'slug' => $slug]);
    $ch = curl_init(API_URL . '/categories/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Đổi method sang PUT
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
    throw new Exception($error['detail'] ?? 'Lỗi khi cập nhật danh mục');
}

// Xóa danh mục
function deleteCategory($id) {
    $ch = curl_init(API_URL . '/categories/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Đổi method sang DELETE
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) return true;
    
    $error = json_decode($response, true);
    throw new Exception($error['detail'] ?? 'Lỗi khi xóa danh mục');
}
?>