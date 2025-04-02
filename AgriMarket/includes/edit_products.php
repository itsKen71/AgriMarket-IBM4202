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

    $upload_dir = "../Assets/img/product_img/"; // Upload directory for image
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Image upload
    if (!empty($_FILES["product_image"]["name"])) {
        $image_name = basename($_FILES["product_image"]["name"]);
        $target_file = $upload_dir . $image_name;
        $image_path = "Assets/img/product_img/" . $image_name; 

        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) 
        {
            if (file_exists($current_image)) 
            {
                unlink($current_image); // Successfully uploaded the new image, remove the old image if exists
            }
        } else {
            echo "Error uploading image.";
            exit();
        }
    } else {
        $image_path = $current_image; // If no new image, use the existing image
    }

    // Update the product in the database
    $stmt = $conn->prepare("UPDATE product 
                            SET category_id = ?, product_image = ?, description = ?, stock_quantity = ?, weight = ?, unit_price = ? 
                            WHERE product_id = ?");
    $stmt->bind_param("issdids", $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_id);

    if ($stmt->execute()) {
        header("Location: ../Modules/vendor/product_listings.php?edit=success");
        exit();
    } else {
        echo "Error updating product: " . $stmt->error;
    }
}
?>
