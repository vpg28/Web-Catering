<?php
session_start();
ob_start(); 

include 'db.php';  

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Set timezone
date_default_timezone_set('Asia/Jakarta'); 

$user_id = 1;

// get user, chat history
$user_info = fetchUser($mysqli, 1);
$chat_info = fetchChatHistory($mysqli, $user_id); 

$username = $user_info['name']; 
$isi_pesan = $chat_info['isi_pesan']; 
$status = $chat_info['status']; 
$time = $chat_info['time']; 

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Display Typed Message
  $data = json_decode(file_get_contents('php://input'), true);
  if (isset($data['message'])) {
      $message = $data['message'];
      
      // Save Message
      $query = "INSERT INTO chat (isi_pesan, time,status,user_id) VALUES (?, NOW(), 'dibaca',1)";
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("s",$message);
      $stmt->execute();
      
      if ($stmt->affected_rows > 0) {
        $response = [
            'success' => true,
            'message' => 'Pesan berhasil disimpan.',
            'timestamp' => date('H:i'),
            'status' => 'pending',
            'username' => $username
        ];
    } else {
        $response = [
            'success' => false,
            'error' => 'Gagal menyimpan pesan.'
        ];
    }
      echo json_encode($response);
  } else {
      echo json_encode(['success' => false, 'error' => 'Tidak ada message.']);
  }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $query = "SELECT * FROM chat";
  $result = $mysqli->query($query);
  $statuses = [];
  while ($row = $result->fetch_assoc()) {
      $status = [
          'timestamp' => $row['time'],
          'status' => $row['status'] 
      ];
      $statuses[] = $status;
  }
  echo json_encode(['success' => true, 'statuses' => $statuses]);
} else {
  echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>