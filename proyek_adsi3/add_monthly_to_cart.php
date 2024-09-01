<?php
session_start();
include 'db.php';  

$user_id = 1;

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // mengambil tanggal
    $current_date = date('Y-m-d');

    // mengambil tanggal awal bulan
    $start_year = date('Y', strtotime($start_date));
    $start_month = date('m', strtotime($start_date));

    error_log("Start date: $start_date");
    error_log("End date: $end_date");
    error_log("Current date: $current_date");
    error_log("Start year: $start_year");
    error_log("Start month: $start_month");

    // get mennu untuk sebulan
    $query = "SELECT id, date FROM menus WHERE date BETWEEN ? AND ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $menu_ids = [];
    while ($row = $result->fetch_assoc()) {
        $menu_ids[] = $row['id'];
    }

    if (!empty($menu_ids)) {
        $query = "INSERT INTO cart (user_id, menu_id, quantity) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);

        foreach ($menu_ids as $menu_id) {
            $quantity = 1; 
            $stmt->bind_param("iii", $user_id, $menu_id, $quantity);
            $stmt->execute();
        }

        $response = ['success' => true, 'message' => 'Monthly package added to cart.'];
    } else {
        $response = ['success' => false, 'message' => 'No menus found for the specified month.'];
    }
} else {
    $response = ['success' => false, 'message' => 'Start date and end date are required.'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
