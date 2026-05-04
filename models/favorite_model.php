<?php
function getUserFavorites($user_id) {
    $ch = curl_init(API_URL . '/users/' . $user_id . '/favorites');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $res = json_decode($response, true);
    return $res['data'] ?? [];
}