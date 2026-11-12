<?php
// POST /api/variants_bulk.php
// body: variant_ids[]=1&variant_ids[]=2
header('Content-Type: application/json; charset=utf-8');

$mysqli = new mysqli('localhost','root','','athletic_hub_db');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connection error']);
    exit;
}

$ids = $_POST['variant_ids'] ?? [];
if (!is_array($ids) || count($ids) === 0) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$stmt = $mysqli->prepare("SELECT pv.id, pv.product_id, pv.variant_name, pv.price, pv.stock, pv.image, p.title
    FROM product_variants pv
    JOIN products p ON p.id = pv.product_id
    WHERE pv.id IN ($placeholders)");
$stmt->bind_param($types, ...array_map('intval', $ids));
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);