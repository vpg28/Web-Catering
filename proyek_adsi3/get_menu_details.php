<?php
include 'db.php';

if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $query = "SELECT * FROM menus WHERE date = ? LIMIT 3";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $menus = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($menus);
} else {
    echo json_encode(['error' => 'No date provided']);
}
?>
