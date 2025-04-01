<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];
    $category_id = $_POST['category_id'];

    // Ensure all required values are received
    if (empty($product_id) || empty($description) || empty($stock_quantity) || empty($weight) || empty($unit_price) || empty($category_id)) {
        die("Error: Missing required fields.");
    }

    $sql = "UPDATE product SET description = ?, stock_quantity = ?, weight = ?, unit_price = ?, category_id = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("siddii", $description, $stock_quantity, $weight, $unit_price, $category_id, $product_id);
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
