<?php
include 'database.php'; 

$db = new Database();
$productClass = new Product($db);

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
        $image_path = $productClass->uploadProductImage($_FILES["product_image"]);

        // Set product properties using setter methods
        $productClass->setVendorId($vendor_id);
        $productClass->setCategoryId($category_id);
        $productClass->setProductName($product_name);
        $productClass->setProductImage($image_path);
        $productClass->setDescription($description);
        $productClass->setStockQuantity($stock_quantity);
        $productClass->setWeight($weight);
        $productClass->setUnitPrice($unit_price);
        $productClass->setProductStatus($product_status);

        // Insert the product into the database
        $insertSuccess = $productClass->insertProduct();

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
