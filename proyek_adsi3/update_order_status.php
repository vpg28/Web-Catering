<?php
include 'db.php';

header('Content-Type: application/json'); 

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orderId'])) {
    $order_id = $data['orderId'];

    error_log("Checking all items completion for order_id: $order_id");

    // cek jika semua orderan sudah selesai
    $check_query = "
        SELECT COUNT(*) as incomplete_count
        FROM order_items
        WHERE order_id = ? AND item_status != 'selesai'
    ";
    $check_stmt = $mysqli->prepare($check_query);
    if (!$check_stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $incomplete_count = $check_result->fetch_assoc()['incomplete_count'];
    $check_stmt->close();

    if ($incomplete_count == 0) {
        // Update order status menjadi selesai
        $update_query = "UPDATE orders SET payment_status = 'selesai' WHERE id = ?";
        $update_stmt = $mysqli->prepare($update_query);
        if (!$update_stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
            exit;
        }
        $update_stmt->bind_param("i", $order_id);
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $update_stmt->error]);
        }
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Pesanan selesai.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
}
?>
