<?php
include 'db.php';  

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID not provided']);
    exit;
}

$order_id = $data['order_id'];

// update status di order
$update_query = "
    UPDATE orders 
    SET payment_status = 'diproses' 
    WHERE id = ?
";
$update_stmt = $mysqli->prepare($update_query);
if (!$update_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}
$update_stmt->bind_param("i", $order_id);
$update_stmt->execute();

if ($update_stmt->error) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $update_stmt->error]);
    exit;
}

// update status di order item
$update_items_query = "
    UPDATE order_items 
    SET item_status = 'diproses' 
    WHERE order_id = ?
";
$update_items_stmt = $mysqli->prepare($update_items_query);
if (!$update_items_stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}
$update_items_stmt->bind_param("i", $order_id);
$update_items_stmt->execute();

if ($update_items_stmt->error) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $update_items_stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Payment and order items status updated']);
?>
