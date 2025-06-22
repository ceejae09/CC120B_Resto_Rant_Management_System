<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $username = $_SESSION['username'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $date_to_avail = $_POST['date_to_avail'];

    $conn = new mysqli("localhost", "root", "", "resto_rant_management_system");
    if ($conn->connect_error) {
        die("DB Error: " . $conn->connect_error);
    }

    // Get room info (price + name)
    $room_stmt = $conn->prepare("SELECT name, price FROM rage_rooms WHERE id = ?");
    $room_stmt->bind_param("i", $room_id);
    $room_stmt->execute();
    $room_stmt->bind_result($room_name, $price);
    $room_stmt->fetch();
    $room_stmt->close();


    // Insert into transactions table
    $trans_stmt = $conn->prepare("INSERT INTO transactions (username, room_id, room_name, price, date_to_avail) VALUES (?, ?, ?, ?, ?)");
    $trans_stmt->bind_param("sisss", $username, $room_id, $room_name, $price, $date_to_avail);
    $trans_stmt->execute();
    $trans_stmt->close();

    $conn->close();

    header("Location: transaction_request.php?booked=success");
    exit;
}
?>
