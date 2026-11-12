<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$mysqli = new mysqli('localhost', 'root', '', 'athletic_hub');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}
$mysqli->set_charset('utf8mb4');
$user_id = intval($_SESSION['user_id']);
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$items_json = $_POST['items_json'] ?? '[]';
$items = json_decode($items_json, true);
if (!is_array($items) || !$items) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Пустой заказ']); exit;
}
if (!$phone || !$address || !$city) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Контакты обязательны']); exit;
}
$mysqli->begin_transaction();
try {
    $sel = $mysqli->prepare('SELECT pv.id, pv.product_id, pv.price, pv.stock FROM product_variants pv WHERE pv.id=? FOR UPDATE');
    $ins = $mysqli->prepare('INSERT INTO orders (user_id, phone, address, city, postal_code, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, "pending", NOW())');
    $insitem = $mysqli->prepare('INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)');
    $updst = $mysqli->prepare("UPDATE product_variants SET stock=stock-? WHERE id=?");
    $total = 0;
    $validated = [];
    foreach ($items as $it) {
        $vid = intval($it['variant_id']);
        $qty = max(1, intval($it['quantity']));
        $sel->bind_param('i', $vid); $sel->execute(); $r=$sel->get_result();
        if (!$r->num_rows) throw new Exception("Вариант $vid не найден");
        $v = $r->fetch_assoc();
        if (intval($v['stock']) < $qty) throw new Exception("Нет в наличии");
        $validated[] = [$vid, $v['product_id'], $qty, floatval($v['price'])];
        $total += floatval($v['price']) * $qty;
    }
    $ins->bind_param('issssd', $user_id, $phone, $address, $city, $postal_code, $total);
    $ins->execute(); $oid = $ins->insert_id;
    foreach ($validated as $v) {
        [$vid, $pid, $qty, $price] = $v;
        $insitem->bind_param('iiiid', $oid, $pid, $vid, $qty, $price);
        $insitem->execute();
        $updst->bind_param('ii', $qty, $vid); $updst->execute();
    }
    $mysqli->commit();
    echo json_encode(['success' => true, 'order_id' => $oid]);
} catch(Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success'=>false, 'message'=>'Ошибка: '.$e->getMessage()]);
}
?>