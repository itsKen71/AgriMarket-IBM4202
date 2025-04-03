<?php
include 'database.php'; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];
    $category_id = $_POST['category_id'];  
    $product_status = "Pending";  // Default status

    try {
        // Upload the product image
        $image_path = uploadProductImage($_FILES["product_image"]);

        // Insert the product into the database
        $insertSuccess = insertProduct($conn, $vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status);

        if ($insertSuccess) {
            // Redirect on success
            header("Location: ../Modules/vendor/product_listings.php?add=success");
            exit();
        } else {
            echo "Error adding product.";
        }
    } catch (Exception $e) {
        // If image upload fails
        echo $e->getMessage();
    }
}
?>
