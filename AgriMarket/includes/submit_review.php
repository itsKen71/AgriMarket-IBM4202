<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? 1;
$review_description = trim($_POST['review_description']);
$review_date = date('Y-m-d'); 

if (!$product_id ) {
    echo "Missing product.";
    exit;
}

// Check if review already exists
$query = "SELECT * FROM review WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Update existing review
    $update = "UPDATE review SET rating = ?, review_description = ?, review_date = ? WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("issii", $rating, $review_description, $review_date, $user_id, $product_id);
    if ($stmt->execute()) {
        echo "updated";
    } else {
        echo "Error updating review.";
    }
} else {
    // Insert new review
    $insert = "INSERT INTO review (product_id, user_id, rating, review_description, review_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("iiiss", $product_id, $user_id, $rating, $review_description, $review_date);
    if ($stmt->execute()) {
        echo "inserted";
    } else {
        echo "Error inserting review.";
    }
}

$conn->close();
?>
