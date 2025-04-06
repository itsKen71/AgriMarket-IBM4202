<?php
session_start();
include 'database.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$review_description = trim($_POST['review']) ?? '';
date_default_timezone_set('Asia/Kuala_Lumpur');
$review_date = date('Y-m-d');

// Basic validation
if (!$product_id || !$rating || $rating < 1 || $rating > 5 || empty($review_description)) {
    http_response_code(400);
    echo "Invalid review submission.";
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO review (product_id, user_id, rating, review_description, review_date) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $product_id, $user_id, $rating, $review_description, $review_date);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Review submitted successfully!";
    } else {
        http_response_code(500);
        echo "Failed to submit review.";
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
