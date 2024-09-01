<?php
include 'db.php';  

ini_set('display_errors', 1);
error_reporting(E_ALL);

function fetchMenus($year, $month, $day = null, $mysqli) {
    $lastDay = date("Y-m-t", strtotime("$year-$month-01"));
    if ($day) {
        $query = "SELECT DAY(date) as day, menu_name, description, price FROM menus WHERE date = ?";
        $stmt = $mysqli->prepare($query);
        $date = "$year-$month-$day";
        $stmt->bind_param("s", $date);
    } else {
        $query = "SELECT DAY(date) as day, menu_name, description, price FROM menus WHERE date BETWEEN ? AND ?";
        $stmt = $mysqli->prepare($query);
        $startDate = "$year-$month-01";
        $stmt->bind_param("ss", $startDate, $lastDay);
    }
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $mysqli->error);
        return [];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        error_log("Failed to execute query: " . $stmt->error);
        return [];
    }

    $menus = [];
    while ($row = $result->fetch_assoc()) {
        $day = $row['day'];
        if (!isset($menus[$day])) {
            $menus[$day] = [];
        }
        $menus[$day][] = [
            'menu_name' => $row['menu_name'],
            'description' => $row['description'],
            'price' => $row['price']
        ];
    }

    error_log(print_r($menus, true));

    return $menus;
}

$month = isset($_GET['month']) ? $_GET['month'] : date('m'); 
$year = isset($_GET['year']) ? $_GET['year'] : date('Y'); 
$day = isset($_GET['day']) ? $_GET['day'] : null; 

if ($month && $year) {
    $menus = fetchMenus($year, $month, $day, $mysqli);
    header('Content-Type: application/json');
    echo json_encode($menus);
} else {
    echo json_encode(['error' => 'Month and year parameters are required']);
}
?>
