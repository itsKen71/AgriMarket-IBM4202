<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

$data = json_decode(file_get_contents('php://input'), true);

$userId = $_SESSION['user_id'];
$orderId = $data['order_id'] ?? null;
$paymentId = $data['payment_id'] ?? null;

if (!$orderId || !$paymentId) {
    http_response_code(400);
    die('Missing required fields');
}

$db = new Database();
$conn = $db->conn;

try {
    $conn->begin_transaction();
    
    $currentDate = date('Y-m-d');
    
    foreach ($data['products'] as $productId => $productData) {
        $reason = $productData['reason'] ?? '';
        $amount = $productData['amount'] ?? 0;
        
        $stmt = $conn->prepare("
            INSERT INTO refund (
                product_id, order_id, payment_id, user_id, 
                refund_amount, refund_date, refund_status, reason
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)
        ");
        $stmt->bind_param(
            "iiiidss", 
            $productId, 
            $orderId, 
            $paymentId, 
            $userId,
            $amount,
            $currentDate,
            $reason
        );
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    die('Database error: ' . $e->getMessage());
}
?>