<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id']; 
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];
    $category_id = $_POST['category_id'];  
    $current_image = $_POST['current_image']; 

    try {
        // Upload the product image (or use the existing image if no new one)
        $image_path = updateProductImage($_FILES["product_image"], $current_image);

        // Update the product in the database
        $updateSuccess = updateProduct($conn, $product_id, $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price);

        if ($updateSuccess) {
            // Redirect on success
            header("Location: ../Modules/vendor/product_listings.php?edit=success");
            exit();
        } else {
            // If the update fails
            echo "Error updating product.";
        }
    } catch (Exception $e) {
        // If image upload fails
        echo $e->getMessage();
    }
}
?>
