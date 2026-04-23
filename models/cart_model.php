<?php
function getCartFromDB($user_id) {
    $ch = curl_init(API_URL . '/cart/' . $user_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true)['data'] ?? [];
}

function addToCartDB($user_id, $product_id, $quantity) {
    $data = json_encode(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);
    $ch = curl_init(API_URL . '/cart/add');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function removeFromCartDB($user_id, $product_id) {
    $ch = curl_init(API_URL . '/cart/remove/' . $user_id . '/' . $product_id);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}