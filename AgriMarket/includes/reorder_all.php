<?php
require 'database.php';
session_start();

$userId = $_SESSION['user_id'];
$quantities = $_POST['quantities'];

foreach ($quantities as $productId => $quantity) {
    $productId = intval($productId);
    $quantity = intval($quantity);

    // Get stock
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stock = $row['stock_quantity'];

    if ($quantity <= 0 || $quantity > $stock) {
        echo json_encode(['success' => false, 'message' => "Invalid quantity for product ID $productId"]);
        exit;
    }

    // Check if in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        $stmt->execute();
    }
}

echo json_encode(['success' => true]);
?>