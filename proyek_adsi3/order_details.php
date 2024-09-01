<?php
include 'db.php'; 

// memeriksa order id
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    if (!filter_var($order_id, FILTER_VALIDATE_INT)) {
        die("Invalid order ID.");
    }
    // get order, user, order detail
    $order_info = fetchOrderHistory($mysqli, $order_id);
    $user_info = fetchUserDetails($mysqli, 1); 
    $orders_by_date = fetchDetailOrder($mysqli, $order_id);

    $order_status = getOrderStatus($mysqli, $order_id); // get status di dalam get order
    $total_price = $order_info['total_price']; // total price di dalam get order
    $payment_method = $order_info['payment_method']; // payment method di dalam order

} else {
    die("Order ID is required.");
}

function fetchDetailOrder($mysqli, $order_id) {
    $order_detail_query = "
        SELECT 
            oi.id as order_item_id,
            m.menu_name, 
            m.price,
            oi.item_status,
            m.date as menu_date 
        FROM 
            order_items oi
        JOIN 
            menus m ON oi.menu_id = m.id 
        WHERE 
            oi.order_id = ?
        ORDER BY
            m.date
    ";
    $order_detail_stmt = $mysqli->prepare($order_detail_query);
    if (!$order_detail_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $order_detail_stmt->bind_param("i", $order_id);
    $order_detail_stmt->execute();
    $order_detail_result = $order_detail_stmt->get_result();
    if (!$order_detail_result) {
        die("Execute failed: " . $order_detail_stmt->error);
    }

    $orders_by_date = [];
    while ($row = $order_detail_result->fetch_assoc()) {
        $orders_by_date[$row['menu_date']][] = $row;
    }

    $order_detail_stmt->close();

    return $orders_by_date;
}

function fetchOrderHistory($mysqli, $order_id) {
    $order_query = "
        SELECT 
            o.total_price,
            pm.method_name
        FROM 
            orders o
        JOIN 
            payment_methods pm ON o.payment_id = pm.id
        WHERE 
            o.id = ?
    ";
    $order_stmt = $mysqli->prepare($order_query);
    if (!$order_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    if (!$order_result) {
        die("Execute failed: " . $order_stmt->error);
    }
    $order = $order_result->fetch_assoc();
    $order_stmt->close();

    return [
        'total_price' => $order['total_price'],
        'payment_method' => $order['method_name']
    ];
}

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
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    return $user;
}

function getOrderStatus($mysqli, $order_id) {
    $status_query = "SELECT payment_status FROM orders WHERE id = ?";
    $status_stmt = $mysqli->prepare($status_query);
    if (!$status_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $status_stmt->bind_param("i", $order_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    if (!$status_result || $status_result->num_rows === 0) {
        die("Order not found.");
    }
    $order_status = $status_result->fetch_assoc()['payment_status'];
    $status_stmt->close();

    return $order_status;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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
            float: right;
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
    </style>
</head>
<body>
<div id="orderModal" class="modal">
    <div class="modal-content">
        <h2>Order Details</h2>
        <hr>
        <div>
            <h3>Pengguna:</h3>
            <?php if ($user_info): ?>
                <p><strong>Name:</strong> <?= htmlspecialchars($user_info['name']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($user_info['address']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user_info['phone']) ?></p>
            <?php else: ?>
                <p>User information not found.</p>
            <?php endif; ?>
        </div>
        <hr>

        <h3>Pembelian:</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Menu Names</th>
                    <th>Prices</th>
                    <?php if ($order_status === 'diproses' || $order_status === 'cod'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders_by_date as $date => $orders): ?>
                    <?php $first = true; ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <?php if ($first): ?>
                                <td rowspan="<?= count($orders) ?>"><?= htmlspecialchars($date) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($order['menu_name']) ?></td>
                            <td><?= htmlspecialchars($order['price']) ?></td>
                            <?php if ($first): ?>
                                <?php $first = false; ?>
                                <?php if ($order_status === 'diproses' || $order_status === 'cod diproses'): ?>
                                    <td rowspan="<?= count($orders) ?>" class="action-cell">
                                        <button class="selesai" data-orderitemid="<?= $order['order_item_id'] ?>" data-orderid="<?= $order_id ?>" data-menudate="<?= $date ?>" onclick="completedOrderStatusDetail(this)">Selesai</button>
                                    </td>
                                <?php elseif ($order_status === 'selesai'): ?>
                                    <td rowspan="<?= count($orders) ?>"><span>Selesai</span></td>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>
        <div>
            <h3>Pembayaran :</h3>
            <p><strong>Total Harga:</strong> <?= $total_price ?></p>
            <p><strong>Metode Pembayaran:</strong> <?= $payment_method ?></p>
        </div>
        <hr>

        <?php if ($order_status === 'diproses'): ?>
            <p id="orderStatus" class="bold yellow-bg">Pesanan diproses</p>
        <?php elseif ($order_status === 'dibatalkan'): ?>
            <p class="bold red-bg">Pesanan dibatalkan</p>
        <?php elseif ($order_status === 'cod'): ?>
            <p id="orderStatus" class="bold yellow-bg">Pesanan COD</p>
        <?php endif; ?>

        <button class="close" onclick="closeModal()">Close</button>
        <br><br>
    </div>
</div>

<script src="order.js"></script>
</body>
</html>
