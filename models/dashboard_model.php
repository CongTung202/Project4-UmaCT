<?php
function getDashboardStats() {
    $ch = curl_init(API_URL . '/dashboard/stats');
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
?>