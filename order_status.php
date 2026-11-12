<?php
// POST: order_id
header('Content-Type: application/json; charset=utf-8');
session_start();
if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$mysqli = new mysqli('localhost','root','','athletic_hub');
$order_id = intval($_POST['order_id'] ?? 0);
$user_id = intval($_SESSION['user_id']);

$stmt = $mysqli->prepare("SELECT id, status, created_at, total_amount FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Не найден']); exit; }
$order = $res->fetch_assoc();
echo json_encode(['success'=>true,'order'=>$order]);