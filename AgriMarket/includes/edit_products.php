<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];

    $stmt = $conn->prepare("UPDATE product SET product_name=?, description=?, stock_quantity=?, weight=?, unit_price=? WHERE product_id=?");
    $stmt->bind_param("ssidsdi", $product_name, $description, $stock_quantity, $weight, $unit_price, $product_id);

    if ($stmt->execute()) {
        header("Location: ../Modules/vendor/product_listings.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>
