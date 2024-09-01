<?php
session_start();
include 'db.php';

if (isset($_POST['voucher_code'])) {
    $voucherCode = $_POST['voucher_code'];
    $isValid = checkVoucher($voucherCode, $mysqli);

    if ($isValid) {
        $discountRate = getDiscountRate($voucherCode, $mysqli);
        $_SESSION['discount'] = calculateDiscount($_SESSION['cart'], $discountRate);
        echo json_encode(['status' => 'success', 'discount' => $_SESSION['discount']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Voucher not valid']);
    }
}

function checkVoucher($code, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM vouchers WHERE code = ? AND expiration_date >= CURDATE()");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function getDiscountRate($voucherCode, $mysqli) {
    $stmt = $mysqli->prepare("SELECT discount_rate FROM vouchers WHERE code = ?");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['discount_rate'];
    }
    return 0;  
}

function calculateDiscount($cart, $rate) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['menus']['price'];
    }
    return $total * $rate;
}
?>
