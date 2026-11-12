<?php
// GET /api/get_variants.php?product_id=NN
// Возвращает JSON массив вариантов (id, variant_name, price, stock, image)

header('Content-Type: application/json; charset=utf-8');

$mysqli = new mysqli('localhost','root','','athletic_hub');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connection error']);
    exit;
}

$product_id = intval($_GET['product_id'] ?? 0);
if ($product_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, variant_name, price, stock, image FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
$variants = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode($variants);