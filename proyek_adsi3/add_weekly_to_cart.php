<?php
session_start();
include 'db.php'; 

$user_id = 1; 

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Ambil tanggal
    $current_date = date('Y-m-d');
    
    // ambil tanggal awal minggu
    $start_of_current_week = date('Y-m-d', strtotime('monday this week'));
    $end_of_current_week = date('Y-m-d', strtotime('sunday this week'));

    error_log("Start date: $start_date");
    error_log("End date: $end_date");
    error_log("Start of current week: $start_of_current_week");
    error_log("End of current week: $end_of_current_week");

    // cek apakah minggu yang dipilih adalah minggu ini
    if (($start_date >= $start_of_current_week && $start_date <= $end_of_current_week) || 
        ($end_date >= $start_of_current_week && $end_date <= $end_of_current_week)) {
        $response = ['success' => false, 'message' => 'Paket minggu ini sudah lewat.'];
    } else {
        // get menu seminggu
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

            $response = ['success' => true, 'message' => 'Weekly package added to cart.'];
        } else {
            $response = ['success' => false, 'message' => 'No menus found for the specified week.'];
        }
    }
} else {
    $response = ['success' => false, 'message' => 'Start date and end date are required.'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
