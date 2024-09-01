<?php
session_start();

if (!isset($_SESSION['payment_info'])) {
    die("No payment information found.");
}

$payment_info = $_SESSION['payment_info'];
$total = $payment_info['total'];
$payment_id = $payment_info['payment_id'];
$payment_deadline = $payment_info['payment_deadline'];
$order_id = $payment_info['order_id'] ?? null; 

if ($order_id === null) {
    die("Order ID not found in payment information.");
}

unset($_SESSION['payment_info']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Instructions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        p, ul {
            margin-bottom: 20px;
        }
        ul {
            padding-left: 20px;
        }
        .tutorial-content {
            display: none;
            padding: 10px;
            background: #f9f9f9;
            border-left: 4px solid #ccc;
            margin-bottom: 10px;
        }
        .tutorial-content.active {
            display: block;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .copy-button {
            background: #28a745;
            margin-left: 10px;
        }
        .copy-button:hover {
            background: #218838;
        }
    </style>
    <script>
        function toggleTutorial(id) {
            const content = document.getElementById(id);
            content.classList.toggle('active');
        }

        function copyToClipboard(elementId) {
            const copyText = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(copyText).then(() => {
                alert("Virtual Account Number copied to clipboard");
                updatePaymentStatus(); // update status after button click
            }).catch(err => {
                console.error("Could not copy text: ", err);
            });
        }

        function updatePaymentStatus() {
            fetch('update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order_id: <?= json_encode($order_id) ?> })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Payment confirmed. Order and items status updated to 'Diproses'.");
                } else {
                    alert("Failed to confirm payment. Please try again.");
                }
            })
            .catch(error => {
                console.error("Error confirming payment: ", error);
                alert("An error occurred. Please try again.");
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <?php if ($payment_id == 1): ?>
            <h2>BCA Virtual Account Payment</h2>
            <p><strong>Virtual Account Number:</strong> <span id="va-number">xxxxxxxxxxxxxxxx</span> <button class="copy-button" onclick="copyToClipboard('va-number')">Copy</button></p>
            <p><strong>Total Amount:</strong> <?= htmlspecialchars($total) ?></p>
            <p><strong>Payment Deadline:</strong> <?= htmlspecialchars($payment_deadline) ?></p>
            <p><strong>Payment Methods:</strong></p>
            <ul>
                <li><a href="#" onclick="toggleTutorial('atm-tutorial')">ATM BCA</a></li>
                <div id="atm-tutorial" class="tutorial-content">
                    <p>1. Insert your ATM card and enter your PIN.</p>
                    <p>2. Select 'Other Transactions'.</p>
                    <p>3. Select 'Transfer'.</p>
                    <p>4. Select 'To BCA Virtual Account'.</p>
                    <p>5. Enter the Virtual Account number and the amount.</p>
                    <p>6. Follow the instructions to complete the payment.</p>
                </div>
                <li><a href="#" onclick="toggleTutorial('mbca-tutorial')">m-BCA (BCA Mobile)</a></li>
                <div id="mbca-tutorial" class="tutorial-content">
                    <p>1. Open BCA Mobile and login.</p>
                    <p>2. Select 'm-Transfer'.</p>
                    <p>3. Select 'BCA Virtual Account'.</p>
                    <p>4. Enter the Virtual Account number and the amount.</p>
                    <p>5. Follow the instructions to complete the payment.</p>
                </div>
                <li><a href="#" onclick="toggleTutorial('ibank-tutorial')">Internet Banking BCA</a></li>
                <div id="ibank-tutorial" class="tutorial-content">
                    <p>1. Login to BCA Internet Banking.</p>
                    <p>2. Select 'Transfer Funds'.</p>
                    <p>3. Select 'To BCA Virtual Account'.</p>
                    <p>4. Enter the Virtual Account number and the amount.</p>
                    <p>5. Follow the instructions to complete the payment.</p>
                </div>
            </ul>
        <?php else: ?>
            <h2>Cash on Delivery</h2>
            <p>Your order has been placed successfully.</p>
            <p>Please be ready to pay the total amount of <strong><?= htmlspecialchars($total) ?></strong> when your order arrives.</p>
            <p>Our delivery person will collect the payment at the time of delivery.</p>
        <?php endif; ?>
        
        <button onclick="window.location.href='order_history.php'">Go to Order History</button>
    </div>
</body>
</html>
