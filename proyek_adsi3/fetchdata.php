<?php
include 'config.php';

$query = "SELECT * FROM menus ORDER BY date ASC";
$result = $conn->query($query);

$menus = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $menus[] = [
            'date' => $row['date'],
            'items' => explode(',', $row['items'])
        ];
    }
    echo json_encode($menus);
} else {
    echo json_encode([]);
}
$conn->close();
?>
