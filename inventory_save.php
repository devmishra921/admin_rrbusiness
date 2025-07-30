<?php
// inventory_save.php – Insert / Update दोनों हैंडल करता है
session_start();
if (!isset($_SESSION['admin_id'])) { http_response_code(401); exit; }

require 'db_connect.php';

// 🔒 Basic sanitisation
$data = filter_input_array(INPUT_POST, [
  'id'             => FILTER_VALIDATE_INT,
  'product_id'     => FILTER_VALIDATE_INT,
  'product_code'   => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
  'quantity'       => FILTER_VALIDATE_FLOAT,
  'unit_price'     => FILTER_VALIDATE_FLOAT,
  'purchase_price' => FILTER_VALIDATE_FLOAT,
  'gst_percent'    => FILTER_VALIDATE_FLOAT,
  'hsn_code'       => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
  'unit'           => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
  'net_weight'     => FILTER_VALIDATE_FLOAT,
  'reorder_level'  => FILTER_VALIDATE_FLOAT,
  'status'         => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
]);

// required fields
if (!$data['product_id'] || !$data['product_code'] || !$data['quantity']) {
    http_response_code(422);
    echo 'Product ID, Code, Quantity are required.';
    exit;
}

// Derived
$data['unit_price']     = $data['unit_price']     ?? 0;
$data['purchase_price'] = $data['purchase_price'] ?? 0;
$data['total_value']    = $data['quantity'] * $data['purchase_price'];

if ($data['id']) {
    /* -------- UPDATE -------- */
    $sql = "UPDATE inventory 
              SET product_id     = ?,
                  product_code   = ?,
                  quantity       = ?,
                  unit_price     = ?,
                  purchase_price = ?,
                  gst_percent    = ?,
                  hsn_code       = ?,
                  unit           = ?,
                  net_weight     = ?,
                  reorder_level  = ?,
                  status         = ?,
                  total_value    = ?,
                  last_updated   = NOW()
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'isddddssssddii',
        $data['product_id'],
        $data['product_code'],
        $data['quantity'],
        $data['unit_price'],
        $data['purchase_price'],
        $data['gst_percent'],
        $data['hsn_code'],
        $data['unit'],
        $data['net_weight'],
        $data['reorder_level'],
        $data['status'],
        $data['total_value'],
        $data['id']
    );
} else {
    /* -------- INSERT -------- */
    $sql = "INSERT INTO inventory
              (product_id, product_code, quantity, unit_price, purchase_price,
               gst_percent, hsn_code, unit, net_weight, reorder_level,
               status, total_value, last_updated)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'isddddssssdd',
        $data['product_id'],
        $data['product_code'],
        $data['quantity'],
        $data['unit_price'],
        $data['purchase_price'],
        $data['gst_percent'],
        $data['hsn_code'],
        $data['unit'],
        $data['net_weight'],
        $data['reorder_level'],
        $data['status'],
        $data['total_value']
    );
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo 'DB error: ' . $stmt->error;
    exit;
}
echo 'success';
