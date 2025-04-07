<?php
session_start();
include 'database.php';

$user_id = $_SESSION['user_id'];
$products = $_POST['products'] ?? [];

if (empty($products)) {
    echo "No products selected.";
    exit;
}

foreach ($products as $product_id => $quantity) {
    $product_id = (int) $product_id;
    $quantity = (int) $quantity;

    if ($quantity < 1) continue;

    // Check stock
    $stockCheck = $conn->prepare("SELECT stock_quantity FROM product WHERE product_id = ?");
    $stockCheck->bind_param("i", $product_id);
    $stockCheck->execute();
    $result = $stockCheck->get_result();

    if ($row = $result->fetch_assoc()) {
        $availableStock = $row['stock_quantity'];
        $quantity = min($quantity, $availableStock);

        // Check if in cart
        $checkCart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkCart->bind_param("ii", $user_id, $product_id);
        $checkCart->execute();
        $cartResult = $checkCart->get_result();

        if ($cartResult->num_rows > 0) {
            // Update
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update->bind_param("iii", $quantity, $user_id, $product_id);
            $update->execute();
        } else {
            // Insert
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $user_id, $product_id, $quantity);
            $insert->execute();
        }
    }
}

echo "success";
$conn->close();
?>
