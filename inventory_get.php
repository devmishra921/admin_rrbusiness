<?php
// inventory_get.php  – एक‑लाइner AJAX फ़ेच
session_start();
if (!isset($_SESSION['admin_id'])) { http_response_code(401); exit; }

require 'db_connect.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode(['error' => 'Invalid ID']); exit; }

$q  = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$q->bind_param('i', $id);
$q->execute();
$res = $q->get_result();
if (!$row = $res->fetch_assoc()) {
    echo json_encode(['error' => 'Item not found']); exit;
}
echo json_encode($row, JSON_NUMERIC_CHECK);
