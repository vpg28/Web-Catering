<?php
include 'db.php'; 

header('Content-Type: application/json'); 

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orderId']) && isset($data['menuDate'])) {
    $order_id = $data['orderId'];
    $menu_date = $data['menuDate'];

    error_log("Updating item status for order_id: $order_id, menu_date: $menu_date");

    // Update item status menjadi selesai
    $update_query = "
        UPDATE order_items 
        SET item_status = 'selesai' 
        WHERE order_id = ? 
        AND menu_id IN (
            SELECT id 
            FROM menus 
            WHERE date = ?
        )
    ";
    $update_stmt = $mysqli->prepare($update_query);
    if (!$update_stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }
    $update_stmt->bind_param("is", $order_id, $menu_date);
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $update_stmt->error]);
    }
    $update_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Order ID and Menu Date are required.']);
}
?>
