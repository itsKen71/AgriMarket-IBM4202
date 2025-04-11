<?php
include 'database.php';

$db = new Database();
$productClass = new Product($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id']; 
    $description = $_POST['description'];
    $stock_quantity = $_POST['stock_quantity'];
    $weight = $_POST['weight'];
    $unit_price = $_POST['unit_price'];
    $category_id = $_POST['category_id'];  
    $current_image = $_POST['current_image']; 
    $product_status = $_POST['product_status'];
    if ($product_status === 'Rejected') {
        $product_status = 'Pending'; // Change status to 'Pending'
    } else {
        $product_status = 'Approved'; // Change status to 'Approved' for all other cases
    }
    try {
        // Upload the product image (or use the existing image if no new one)
        $image_path = $productClass->updateProductImage($_FILES["product_image"], $current_image);

        // Update the product in the database
        $updateSuccess = $productClass->updateProduct($product_id, $category_id, $image_path,
         $description, $stock_quantity, $weight, $unit_price, $product_status);

        if ($updateSuccess) {
            // Redirect on success
            header("Location: ../Modules/vendor/product_listings.php?edit=success");
            exit();
        } else {
            // If the update fails
            echo "Error updating product.";
            exit();
        }
    } catch (Exception $e) {
        // If image upload fails
        echo $e->getMessage();
    }
}
?>
