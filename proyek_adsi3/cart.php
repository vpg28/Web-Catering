<?php
session_start();
ob_start(); 

include 'db.php';  

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Asia/Jakarta'); 

$user_id = 1;

// hapus item pada cart
function removeItemsFromCart($mysqli, $user_id, $remove_date) {
    $remove_query = "
        DELETE FROM cart 
        WHERE user_id = ? 
        AND menu_id IN (
            SELECT id FROM menus WHERE DATE(date) = ?
        )
    ";
    $remove_stmt = $mysqli->prepare($remove_query);
    if (!$remove_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $remove_stmt->bind_param("is", $user_id, $remove_date);
    $remove_stmt->execute();
    if ($remove_stmt->error) {
        die("Execute failed: " . $remove_stmt->error);
    }
}

// get data customer
function fetchUserDetails($mysqli, $user_id) {
    $user_query = "
        SELECT 
            name, address, phone
        FROM 
            users
        WHERE 
            id = ?
    ";
    $user_stmt = $mysqli->prepare($user_query);
    if (!$user_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if (!$user_result) {
        die("Execute failed: " . $user_stmt->error);
    }
    return $user_result->fetch_assoc();
}

// apply voucher
function applyVoucherCode($mysqli, $voucher_code) {
    $voucher_query = "
        SELECT 
            discount_rate, expiration_date 
        FROM 
            vouchers 
        WHERE 
            code = ?
    ";
    $voucher_stmt = $mysqli->prepare($voucher_query);
    if (!$voucher_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $voucher_stmt->bind_param("s", $voucher_code);
    $voucher_stmt->execute();
    $voucher_result = $voucher_stmt->get_result();
    if (!$voucher_result) {
        die("Execute failed: " . $voucher_stmt->error);
    }

    if ($voucher_result->num_rows > 0) {
        $voucher = $voucher_result->fetch_assoc();
        $today = new DateTime();
        $expiration_date = new DateTime($voucher['expiration_date']);
        
        if ($today <= $expiration_date) {
            return [
                'discount_rate' => (float) $voucher['discount_rate'],
                'message' => "Voucher applied successfully!"
            ];
        } else {
            return ['discount_rate' => 0, 'message' => "Voucher is expired."];
        }
    } else {
        return ['discount_rate' => 0, 'message' => "Invalid voucher code."];
    }
}

// get isi cart
function fetchCartItems($mysqli, $user_id) {
    $cart_query = "
        SELECT 
            DATE(m.date) as menu_date, 
            GROUP_CONCAT(m.menu_name SEPARATOR ', ') as menu_names, 
            SUM(m.price) as total_price,
            GROUP_CONCAT(c.menu_id SEPARATOR ', ') as menu_ids
        FROM 
            cart c 
        JOIN 
            menus m ON c.menu_id = m.id 
        WHERE 
            c.user_id = ?
        GROUP BY 
            DATE(m.date)
    ";
    $cart_stmt = $mysqli->prepare($cart_query);
    if (!$cart_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    if (!$cart_result) {
        die("Execute failed: " . $cart_stmt->error);
    }
    return $cart_result;
}

// get metode pembayaran
function fetchPaymentMethods($mysqli) {
    $payment_query = "
        SELECT 
            id, method_name 
        FROM 
            payment_methods
    ";
    $payment_result = $mysqli->query($payment_query);
    if (!$payment_result) {
        die("Query failed: " . $mysqli->error);
    }
    return $payment_result;
}

// function untuk mencatat order
function processOrderConfirmation($mysqli, $user_id, $total, $discount, $selected_payment_id, $cart_result) {
    $payment_status = ($selected_payment_id == 1) ? "belum bayar" : "cod diproses";
    $item_status = ($selected_payment_id == 1) ? "belum bayar" : "cod diproses";

    $order_query = "
        INSERT INTO orders (user_id, total_price, discount, payment_id, confirmed_at, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $order_stmt = $mysqli->prepare($order_query);
    if (!$order_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $confirmed_at = (new DateTime())->format('Y-m-d H:i:s');
    $order_stmt->bind_param("iddiss", $user_id, $total, $discount, $selected_payment_id, $confirmed_at, $payment_status);
    $order_stmt->execute();
    if ($order_stmt->error) {
        die("Execute failed: " . $order_stmt->error);
    }
    $order_id = $mysqli->insert_id;

    $cart_result->data_seek(0);
    while ($row = $cart_result->fetch_assoc()) {
        $menu_ids = explode(', ', $row['menu_ids']);
        foreach ($menu_ids as $menu_id) {
            $item_query = "
                INSERT INTO order_items (order_id, menu_id, item_status) 
                VALUES (?, ?, ?)
            ";
            $item_stmt = $mysqli->prepare($item_query);
            if (!$item_stmt) {
                die("Prepare failed: " . $mysqli->error);
            }
            $item_stmt->bind_param("iis", $order_id, $menu_id, $item_status);
            $item_stmt->execute();
            if ($item_stmt->error) {
                die("Execute failed: " . $item_stmt->error);
            }
        }
    }

    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $clear_cart_stmt = $mysqli->prepare($clear_cart_query);
    if (!$clear_cart_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();
    if ($clear_cart_stmt->error) {
        die("Execute failed: " . $clear_cart_stmt->error);
    }

    $payment_deadline = (new DateTime())->modify('+1 day')->format('Y-m-d H:i:s');
    $_SESSION['payment_info'] = [
        'total' => $total,
        'discount' => $discount,
        'payment_id' => $selected_payment_id,
        'payment_deadline' => $payment_deadline,
        'order_id' => $order_id
    ];

    header('Location: payment_instructions.php');
    exit;
}

// hapus cart
if (isset($_POST['remove_date'])) {
    $remove_date = $_POST['remove_date'];
    removeItemsFromCart($mysqli, $user_id, $remove_date);
}

// get customer info
$user = fetchUserDetails($mysqli, $user_id);

$discount_rate = 0;
$voucher_message = "";

if (isset($_POST['apply_voucher'])) {
    $voucher_code = $_POST['voucher_code'];
    $voucher_data = applyVoucherCode($mysqli, $voucher_code);
    $discount_rate = $voucher_data['discount_rate'];
    $voucher_message = $voucher_data['message'];
}

$cart_result = fetchCartItems($mysqli, $user_id);
$is_cart_empty = ($cart_result->num_rows === 0);

// hitung subtotal
$subtotal = 0;
$cart_result->data_seek(0);
while ($row = $cart_result->fetch_assoc()) {
    $subtotal += $row['total_price'];
}

// hitung discount and total
$discount = $subtotal * $discount_rate;
$total = $subtotal - $discount;

$payment_result = fetchPaymentMethods($mysqli);

if (isset($_POST['confirm_payment']) && !$is_cart_empty) {
    $selected_payment_id = $_POST['payment_method'];
    processOrderConfirmation($mysqli, $user_id, $total, $discount, $selected_payment_id, $cart_result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .modal {
            display: block;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close, .selesai {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .close:hover, .selesai:hover {
            background-color: #45a049;
        }
        .bold {
            font-weight: bold;
        }
        
        .red-bg {
            background-color: #ea9999; 
        }

        .green-bg {
            background-color: #afe1af; 
        }

        .yellow-bg {
            background-color: #fff294; 
        }
        .action-cell {
            justify-content: center;
            align-items: center;
        }
        hr {
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .back-button:hover {
            background-color: #45a049;
        }
        h1 {
            margin-top: 50px; /* Add margin to move it below the button */
        }
        .confirm-payment-container {
            text-align: left;
        }
    </style>
</head>
<body>
    <button onclick="window.location.href='calendar.php'" class="back-button">Back to Menu</button> 
    <h1>Your Cart</h1>

    <?php if ($is_cart_empty): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <div class="user-info">
            <h2>User Information</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        </div>

        <hr> 

        <form method="POST">
            <label for="voucher_code">Enter Voucher Code:</label>
            <input type="text" id="voucher_code" name="voucher_code" required>
            <button type="submit" name="apply_voucher">Apply Voucher</button>
        </form>
        <p><?= htmlspecialchars($voucher_message) ?></p>

        <hr>

        <table>
            <tr>
                <th>Date</th>
                <th>Menu Names</th>
                <th>Total Price</th>
                <th>Actions</th>
            </tr>
            <?php 
            $cart_result->data_seek(0);
            while ($row = $cart_result->fetch_assoc()): 
            ?>
            <tr>
                <td><?= htmlspecialchars($row['menu_date']) ?></td>
                <td><?= htmlspecialchars($row['menu_names']) ?></td>
                <td><?= htmlspecialchars($row['total_price']) ?></td>
                <td class="action-cell">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="remove_date" value="<?= htmlspecialchars($row['menu_date']) ?>">
                        <button type="submit" class="close">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <hr> 

        <div class="summary">
            <h2>Order Summary</h2>
            <p><strong>Sub Total:</strong> <?= htmlspecialchars($subtotal) ?></p>
            <p><strong>Shipping Cost:</strong> Free</p>
            <p><strong>Discount:</strong> -<?= htmlspecialchars($discount) ?> (<?= htmlspecialchars($discount_rate * 100) ?>%)</p>
            <p><strong>Total:</strong> <?= htmlspecialchars($total) ?></p>
        </div>

        <hr> 

        <form method="POST">
            <h2>Select Payment Method</h2>
            <?php while ($payment_method = $payment_result->fetch_assoc()): ?>
                <div>
                    <input type="radio" id="<?= htmlspecialchars($payment_method['method_name']) ?>" name="payment_method" value="<?= htmlspecialchars($payment_method['id']) ?>" required>
                    <label for="<?= htmlspecialchars($payment_method['method_name']) ?>"><?= htmlspecialchars($payment_method['method_name']) ?></label>
                </div>
            <?php endwhile; ?>
            <div class="confirm-payment-container">
                <button type="submit" name="confirm_payment" class="selesai">Confirm Payment</button>
            </div>
        </form>
    <?php endif; ?>

</body>
</html>



<?php
ob_end_flush(); 
?>
