<?php
header('Content-Type: application/json');

include 'database.php';

// Get the product ID from the query parameters
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    // Return an error if product_id is missing
    echo json_encode(['error' => 'Product ID is missing.']);
    exit();
}

// Fetch the product details from the database
$query = "SELECT p.product_name, p.unit_price, p.stock_quantity, p.description, p.product_image, p.weight, c.category_name
          FROM product p
          JOIN category c ON p.category_id = c.category_id
          WHERE p.product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$product = $result->fetch_assoc();

if ($product) {
    // Return product details as JSON
    echo json_encode($product);
} else {
    // Return an error if no product found
    echo json_encode(['error' => 'Product not found.']);
}

exit();
?>
