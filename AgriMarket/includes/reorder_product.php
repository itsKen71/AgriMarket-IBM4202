<?php
session_start();
include 'database.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id || $quantity < 1) {
    echo "Invalid data.";
    exit;
}

// Check current stock
$stockCheck = $conn->prepare("SELECT stock_quantity FROM product WHERE product_id = ?");
$stockCheck->bind_param("i", $product_id);
$stockCheck->execute();
$stockResult = $stockCheck->get_result();
if ($stockResult->num_rows === 0) {
    echo "Product not found.";
    exit;
}

$row = $stockResult->fetch_assoc();
if ($quantity > $row['stock_quantity']) {
    echo "Quantity exceeds available stock.";
    exit;
}

// Check if product already in cart
$checkCart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$checkCart->bind_param("ii", $user_id, $product_id);
$checkCart->execute();
$result = $checkCart->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $update->bind_param("iii", $quantity, $user_id, $product_id);
    $update->execute();
} else {
    // Insert new row
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $user_id, $product_id, $quantity);
    $insert->execute();
}

echo "success";
$conn->close();
?>
