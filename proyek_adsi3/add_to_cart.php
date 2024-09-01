<?php
session_start();
include 'db.php';  // 

header('Content-Type: application/json');

$user_id = 1;

if (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['day'])) {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $day = $_GET['day'];
    $date = "$year-$month-$day";

    error_log("Incoming parameters - year: $year, month: $month, day: $day");

    // get menu sesuai tanggal
    $query = "SELECT id FROM menus WHERE date = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
        exit;
    }
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $added = false;
        while ($menu = $result->fetch_assoc()) {
            $menu_id = $menu['id'];

            error_log("Menu ID: $menu_id");

            // tambahkan menu ke cart
            $query = "INSERT INTO cart (user_id, menu_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1";
            $stmt = $mysqli->prepare($query);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for cart.']);
                exit;
            }
            $stmt->bind_param("ii", $user_id, $menu_id);
            if ($stmt->execute()) {
                $added = true;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add menu ID ' . $menu_id . ' to cart.']);
                exit;
            }
        }
        if ($added) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No menus were added to cart.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No menu found for the selected date.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid date parameters.']);
}
?>
