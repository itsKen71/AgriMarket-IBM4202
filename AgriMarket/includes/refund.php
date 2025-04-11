<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $orderId = $_POST['order_id'];
    $paymentId = $_POST['payment_id'];
    $userId = $_SESSION['user_id'];
    $refundAmount = $_POST['refund_amount'];
    $reason = $_POST['reason'];
    $refundDate = date('Y-m-d');

    $db = new Database();
    $conn = $db->conn;

    $stmt = $conn->prepare("INSERT INTO refund (product_id, order_id, payment_id, user_id, refund_amount, refund_date, refund_status, reason) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("iiiidss", $productId, $orderId, $paymentId, $userId, $refundAmount, $refundDate, $reason);
    $stmt->execute();
    $stmt->close();

    header("Location: ../Modules/customer/order_history.php?refund=success");
    exit();
}
?>
