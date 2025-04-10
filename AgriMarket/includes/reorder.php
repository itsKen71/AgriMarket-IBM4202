<?php
session_start();
require 'database.php'; // adjust if needed

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = intval($_POST['quantity']);

// Check if already in cart
$checkSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $updateSql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
} else {
    // Insert new
    $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
}

$stmt->execute();
header("Location: ../Modules/customer/order_history.php?reorder=success");
exit;
?>