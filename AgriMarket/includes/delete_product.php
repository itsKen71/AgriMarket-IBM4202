<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $db = new Database();
    $conn = $db->conn;
    // Query to delete the product
    $query = "DELETE FROM product WHERE product_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Check if the deletion was successful
        if ($stmt->affected_rows > 0) {
            // Redirect to product listing page with success message
            header("Location: ../Modules/vendor/product_listings.php?delete=success");
            exit();
        } else {
            echo "Error deleting the product.";
            exit();
        }
    } else {
        // Handle errors in preparation of the statement
        echo "Error preparing the delete query.";
    }
}
?>
