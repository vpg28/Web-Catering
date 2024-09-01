<?php
include 'db.php';  

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$order_id = $data['order_id'] ?? null;
if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Order ID is missing']);
    exit;
}

// update payment status di orders
$update_order_query = "
    UPDATE orders 
    SET payment_status = 'dibatalkan' 
    WHERE id = ? AND payment_status = 'COD diproses'
";
$stmt = $mysqli->prepare($update_order_query);
if ($stmt) {
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        // updet item status di order items
        $update_items_query = "
            UPDATE order_items 
            SET item_status = 'dibatalkan' 
            WHERE order_id = ?
        ";
        $stmt_items = $mysqli->prepare($update_items_query);
        if ($stmt_items) {
            $stmt_items->bind_param('i', $order_id);
            $stmt_items->execute();
            if ($stmt_items->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No items updated']);
            }
            $stmt_items->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Prepare items failed: ' . $mysqli->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No order updated']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Prepare order failed: ' . $mysqli->error]);
}

$mysqli->close();
?>
