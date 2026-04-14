<?php
function getAllBanners() {
    $ch = curl_init(API_URL . '/banners');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return [];
    return json_decode($response, true)['data'] ?? [];
}

function getBannerById($id) {
    $ch = curl_init(API_URL . '/banners/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return null;
    return json_decode($response, true)['data'] ?? null;
}

function addBanner($data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/banners');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) throw new Exception("Lỗi kết nối tới Server Python: " . $err);
    if ($httpCode == 200) return true;
    throw new Exception(json_decode($response, true)['detail'] ?? 'Lỗi khi thêm banner');
}

function updateBanner($id, $data) {
    $jsonData = json_encode($data);
    $ch = curl_init(API_URL . '/banners/' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) throw new Exception("Lỗi kết nối tới Server Python: " . $err);
    if ($httpCode == 200) return true;
    throw new Exception(json_decode($response, true)['detail'] ?? 'Lỗi khi cập nhật banner');
}

function deleteBanner($id) {
    $ch = curl_init(API_URL . '/banners/' . $id);
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
    throw new Exception(json_decode($response, true)['detail'] ?? 'Lỗi khi xóa banner');
}
?>