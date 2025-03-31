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

    // Upload directory for image
    $upload_dir = "../Assets/img/product_img/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Image upload
    $image_name = basename($_FILES["product_image"]["name"]);
    $target_file = $upload_dir . $image_name;
    $image_path = "Assets/img/product_img/" . $image_name; // Corrected path for database storage

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO product (vendor_id, category_id, product_name, product_image, description, stock_quantity, weight, unit_price, product_status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssidds", $vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status);

        if ($stmt->execute()) {
            header("Location: ../Modules/vendor/product_listings.php?add=success");
            exit();
        } else {
            echo "Error adding product: " . $stmt->error;
        }
    } else {
        echo "Error uploading image.";
    }
}
?>
