<?php
require_once '../config/config.php';
require_once '../models/product_model.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if (strlen(trim($q)) < 2) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

$results = searchProducts($q);
echo json_encode(['status' => 'success', 'data' => $results]);