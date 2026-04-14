<?php
require_once __DIR__ . '/../config/config.php';

function uploadImageToAPI($fileArray) {
    // Kiểm tra xem có lỗi khi chọn file không
    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Lỗi khi tải file từ máy tính lên PHP.");
    }

    $tmpFilePath = $fileArray['tmp_name'];
    $fileName = $fileArray['name'];
    $mimeType = $fileArray['type'];

    // Dùng CURLFile của PHP để đóng gói file gửi đi
    $cfile = new CURLFile($tmpFilePath, $mimeType, $fileName);
    $data = ['file' => $cfile];

    $ch = curl_init(API_URL . '/upload');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // Tắt kiểm tra SSL trên localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode == 200 && isset($result['url'])) {
        return $result['url']; // Trả về link Cloudinary
    } else {
        throw new Exception($result['detail'] ?? "Không thể upload ảnh sang API Python.");
    }
}
?>