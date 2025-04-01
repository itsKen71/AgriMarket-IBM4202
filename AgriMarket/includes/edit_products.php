<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];

    // Ensure all required values are received
    if (empty($product_id) || empty($product_name) || empty($description) || empty($stock_quantity) || empty($weight) || empty($unit_price)) {
        die("Error: Missing required fields.");
    }

    $sql = "UPDATE product SET product_name = ?, description = ?, stock_quantity = ?, weight = ?, unit_price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssiddi", $product_name, $description, $stock_quantity, $weight, $unit_price, $product_id);
        if ($stmt->execute()) {
             // Redirect on success
             header("Location: ../Modules/vendor/product_listings.php?edit=success");
             exit();
        } else {
            echo "Error updating product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "SQL Error: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
