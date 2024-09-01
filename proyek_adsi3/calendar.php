<?php
session_start();
ob_start(); 

include 'db.php';  

$user_info = fetchUser($mysqli, 1); 

$username = $user_info['name']; 


function fetchUser($mysqli, $user_id) {
    $user_query = "
        SELECT 
            name
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

    return ['name' => $user['name']];
}

function fetchChatHistory($mysqli, $user_id) {
    $chat_query = "
        SELECT 
            isi_pesan,
            status,
            time
        FROM 
            chat
        WHERE 
            user_id = ?
    ";
    $chat_stmt = $mysqli->prepare($chat_query);
    if (!$chat_stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $chat_stmt->bind_param("i", $user_id);
    $chat_stmt->execute();
    $chat_result = $chat_stmt->get_result();
    if (!$chat_result) {
        die("Execute failed: " . $chat_stmt->error);
    }
    $chat = $chat_result->fetch_assoc();
    $chat_stmt->close();

    return [
        'isi_pesan' => $chat['isi_pesan'],
        'status' => $chat['status'],
        'time' => $chat['time']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Calendar</title>
    <link rel="stylesheet" href="calendar.css">
</head>
<body data-username=<?php echo $username;?>>


    <div id="menu-calendar">
        <div class="header">
            <button id="prev-button">❮</button>
            <span id="monthYear"></span>
            <button id="next-button">❯</button>
            <button id="chatButton">Chat</button>
            <button id="history-button">History</button> 
            <button id="cart-button" onclick="window.location.href='cart.php'">Cart</button> 
        </div>
        <table id="calendar">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="menuModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <div class="menu-image"><img src="" alt="Menu Image"></div>
            <div class="menu-info">
                <p class="menu-date"></p>
                <p class="menu-details"></p>
                <p class="menu-total-price"></p>
                <button class="close-button" onclick="closeModal()">Close</button>
                <button id="buyNowButton">beli sekarang</button>
            </div>
        </div>
    </div>
  
    <div id="subscriptionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSubscriptionModal()">&times;</span>
            <h2>Pilih Jenis Langganan</h2>
            <div class="subscription-options">
                <button id="dailyButton">Harian</button>
                <button id="weeklyButton">Mingguan</button>
                <button id="monthlyButton">Bulanan</button>
            </div>
        </div>
    </div>

    <div id="chatModal" class="modal">
        <div class="chat-container">
            <div class="chat-header">
                <span class="close" onclick="closeChatModal()">&times;</span>
                <h2>Chat</h2>
            </div>
            <div class="chat-body" id="chatBody"></div>
                <div class="chat-footer">
                    <input type="text" id="chatInput" placeholder="Tuliskan pesan anda...">
                    <button id="sendChatButton">Send</button>
            </div>
        </div>
    </div>

    <script src="chat.js"></script>
    <script src="calendar.js"></script>
    <script src="beli_sekarang.js"></script>
    <script src="cart.js"></script>
    <script src="weekly_cart.js"></script>
    <script src="monthly_cart.js"></script>
    <script src="history.js"></script>
    <script src="subscription.js"></script> 
    
</body>
</html>