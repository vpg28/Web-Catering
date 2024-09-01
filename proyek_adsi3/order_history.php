<?php
session_start();
include 'db.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$user_id = 1;

// membatalkan otomatis
function updateOverdueOrders($mysqli) {
    $update_query = "
        UPDATE orders 
        SET payment_status = 'dibatalkan' 
        WHERE payment_status = 'belum bayar' 
        AND confirmed_at < NOW() - INTERVAL 1 DAY
    ";
    if (!$mysqli->query($update_query)) {
        die("Failed to update overdue orders: " . $mysqli->error);
    }
}

function fetchOrderHistory($mysqli, $user_id, $status = '') {
    $order_query = "
        SELECT 
            o.id,
            o.confirmed_at as confirm_date,
            o.total_price,
            o.payment_id,
            p.method_name,
            o.payment_status as order_status
        FROM 
            orders o
        JOIN 
            payment_methods p ON o.payment_id = p.id
        WHERE 
            o.user_id = ?
    ";

    if ($status) {
        $order_query .= " AND o.payment_status = ?";
    }

    $order_stmt = $mysqli->prepare($order_query);
    if (!$order_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }

    if ($status) {
        $order_stmt->bind_param("is", $user_id, $status);
    } else {
        $order_stmt->bind_param("i", $user_id);
    }
    
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    if (!$order_result) {
        die("Execute failed: " . $order_stmt->error);
    }

    $has_orders = $order_result->num_rows > 0;

    return ['result' => $order_result, 'has_orders' => $has_orders];
}


// Update pesanan yang melebihi batas waktu
updateOverdueOrders($mysqli);

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$order_data = fetchOrderHistory($mysqli, $user_id, $status_filter);
$order_result = $order_data['result'];
$has_orders = $order_data['has_orders'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        .tutorial-content { display: none; }
        .tutorial-content.active { display: block; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .no-order-message {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh; /* Mengatur tinggi elemen agar pesan berada di tengah secara vertikal */
            font-size: 24px; /* Ukuran teks */
            color: #555; /* Warna teks */
        }
    </style>
    <script>
        function toggleTutorial(id) {
            var content = document.getElementById(id);
            if (content.classList.contains('active')) {
                content.classList.remove('active');
            } else {
                content.classList.add('active');
            }
        }

        function openModal(orderId, paymentMethod, totalPrice, paymentDeadline) {
            document.getElementById("paymentModal").style.display = "block";
    
            if (paymentMethod === 'BCA Virtual Account') {
                document.getElementById('paymentTitle').innerText = 'BCA Virtual Account Payment';
                document.getElementById('paymentDetails').innerHTML = `
                    <p><strong>Virtual Account Number:</strong> <span id="vaNumber${orderId}">xxxxxxxxxxxxxxxx</span> <button onclick="copyToClipboard('vaNumber${orderId}', ${orderId})">Copy</button></p>
                    <p><strong>Total Amount:</strong> ${totalPrice}</p>
                    <p><strong>Payment Deadline:</strong> ${paymentDeadline}</p>
                    <p><strong>Payment Methods:</strong></p>
                    <ul>
                        <li><a href='#' onclick='toggleTutorial("atm-tutorial")'>ATM BCA</a></li>
                        <div id='atm-tutorial' class='tutorial-content'>
                            <p>1. Insert your ATM card and enter your PIN.</p>
                            <p>2. Select 'Other Transactions'.</p>
                            <p>3. Select 'Transfer'.</p>
                            <p>4. Select 'To BCA Virtual Account'.</p>
                            <p>5. Enter the Virtual Account number and the amount.</p>
                            <p>6. Follow the instructions to complete the payment.</p>
                        </div>
                        <li><a href='#' onclick='toggleTutorial("mbca-tutorial")'>m-BCA (BCA Mobile)</a></li>
                        <div id='mbca-tutorial' class='tutorial-content'>
                            <p>1. Open BCA Mobile and login.</p>
                            <p>2. Select 'm-Transfer'.</p>
                            <p>3. Select 'BCA Virtual Account'.</p>
                            <p>4. Enter the Virtual Account number and the amount.</p>
                            <p>5. Follow the instructions to complete the payment.</p>
                        </div>
                        <li><a href='#' onclick='toggleTutorial("ibank-tutorial")'>Internet Banking BCA</a></li>
                        <div id='ibank-tutorial' class='tutorial-content'>
                            <p>1. Login to BCA Internet Banking.</p>
                            <p>2. Select 'Transfer Funds'.</p>
                            <p>3. Select 'To BCA Virtual Account'.</p>
                            <p>4. Enter the Virtual Account number and the amount.</p>
                            <p>5. Follow the instructions to complete the payment.</p>
                        </div>
                    </ul>
                `;
            } else {
                document.getElementById('paymentTitle').innerText = 'Cash on Delivery';
                document.getElementById('paymentDetails').innerHTML = `
                    <p>Your order has been placed successfully.</p>
                    <p>Please be ready to pay the total amount of <strong>${totalPrice}</strong> when your order arrives.</p>
                    <p>Our delivery person will collect the payment at the time of delivery.</p>
                `;
            }
        }

        function openOrderDetailModal(orderId) {
            fetch(`order_details.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderDetailContent').innerHTML = data;
                    document.getElementById('orderDetailModal').style.display = 'block';
                })
                .catch(error => console.error('Error fetching order details:', error));
        }

        function closeModal() {
            document.getElementById("paymentModal").style.display = "none";
            document.getElementById("orderDetailModal").style.display = "none";
        }

        function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order has been cancelled.');
                location.reload();
            } else {
                alert('Failed to cancel order: ' + data.error);
            }
        })
        .catch(error => console.error('Error cancelling order:', error));
    }
}


function cancelOrderCOD(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch('cancel_order_cod.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order has been cancelled.');
                location.reload();
            } else {
                alert('Failed to cancel order: ' + data.error);
            }
        })
        .catch(error => console.error('Error cancelling order:', error));
    }
}


        function copyToClipboard(elementId, orderId) {
            var copyTextElement = document.getElementById(elementId);
            if (copyTextElement) {
                var copyText = copyTextElement.textContent;
                navigator.clipboard.writeText(copyText).then(function() {
                    alert("Virtual Account Number copied to clipboard");
                    updatePaymentStatus(orderId); // update status setelah button diklik
                }, function(err) {
                    console.error("Could not copy text: ", err);
                });
            } else {
                console.error("Element with ID '" + elementId + "' not found.");
                alert("Failed to copy: element not found.");
            }
        }

        function updatePaymentStatus(orderId) {
    console.log('Updating payment status for order ID:', orderId);
    fetch('update_payment_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert("Payment confirmed. Order status updated to 'Selesai'.");
            // mengupdate status pada tabel
            document.getElementById('orderStatus' + orderId).textContent = 'Selesai';
            // menyembunyikan button cara pembayaran dan button cancel
            document.getElementById('tutorialButton' + orderId).style.display = 'none';
            document.getElementById('cancelButton' + orderId).style.display = 'none';
        } else {
            alert("Failed to confirm payment. Please try again: " + data.error);
        }
    })
    .catch(error => {
        console.error("Error confirming payment: ", error);
        alert("An error occurred. Please try again.");
    });
}


        function hideCancelButton(orderId) {
            const buttonId = `cancelButtonCOD${orderId}`;
            setTimeout(() => {
                const button = document.getElementById(buttonId);
                if (button) {
                    button.style.display = 'none';
                    localStorage.setItem(buttonId, 'hidden');
                }
            }, 120000); // 2 menit
        }

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("button[id^='cancelButtonCOD']").forEach(button => {
                const buttonId = button.id;
                if (localStorage.getItem(buttonId) === 'hidden') {
                    button.style.display = 'none';
                }
            });
        });
    </script>
    <script src="order.js"></script>

</head>
<body>
<h1>Order History</h1>
<form method="GET" action="">
    <label for="status">Filter by Status:</label>
    <select name="status" id="status">
        <option value="">All</option>
        <option value="belum bayar" <?= $status_filter == 'belum bayar' ? 'selected' : '' ?>>Belum Bayar</option>
        <option value="cod diproses" <?= $status_filter == 'cod diproses' ? 'selected' : '' ?>>COD Diproses</option>
        <option value="diproses" <?= $status_filter == 'diproses' ? 'selected' : '' ?>>Diproses</option>
        <option value="dibatalkan" <?= $status_filter == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
        <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
    </select>
    <button type="submit">Filter</button>
</form>
<br>

<?php if ($has_orders): ?>
    <table>
        <tr>
            <th>Confirm Date</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Payment Method</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $order_result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['confirm_date']) ?></td>
            <td id="orderStatus<?= $row['id'] ?>"><?= htmlspecialchars($row['order_status']) ?></td>
            <td><?= htmlspecialchars($row['total_price']) ?></td>
            <td><?= htmlspecialchars($row['method_name']) ?></td>
            <td>
                <?php if ($row['payment_id'] == 1 && $row['order_status'] == 'belum bayar'): ?>
                    <button id="tutorialButton<?= $row['id'] ?>" onclick="openModal('<?= $row['id'] ?>', 'BCA Virtual Account', '<?= htmlspecialchars($row['total_price']) ?>', '<?= htmlspecialchars($row['confirm_date']) ?>')">Payment Tutorial</button>
                    <button id="cancelButton<?= $row['id'] ?>" onclick="cancelOrder('<?= $row['id'] ?>')">Cancel Order</button>
                <?php elseif ($row['payment_id'] == 2 && $row['order_status'] == 'cod diproses'): ?>
                    <button id="cancelButtonCOD<?= $row['id'] ?>" onclick="cancelOrderCOD('<?= $row['id'] ?>'); hideCancelButton('<?= $row['id'] ?>')">Cancel Order</button>
                    <script>hideCancelButton('<?= $row['id'] ?>');</script>
                <?php endif; ?>
                <button onclick="openOrderDetailModal('<?= $row['id'] ?>')">Detail Transaksi</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="no-order-message">
        Tidak ada riwayat data pemesanan
    </div>
<?php endif; ?>
<button onclick="window.location.href='calendar.php'">Back to Home</button>

    <!-- Modal untuk cara pembayaran -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="paymentTitle"></h2>
            <div id="paymentDetails"></div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="orderDetailContent"></div>
        </div>
    </div>
</body>
</html>
