<?php
session_start();
include 'database.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

foreach ($data as $product) {
    $product_id = (int) $product['product_id'];
    $quantity = max(1, min((int) $product['quantity'], 999)); // hard limit, frontend also restricts

    // Check if cart entry exists
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }

    $stmt->execute();
}

http_response_code(200);
exit;
?>