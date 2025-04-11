<?php
session_start();
include 'database.php';


$db = new Database();
$conn = $db->conn;

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$rating = $_POST['rating'];
$review_text = $_POST['review_description'];
$date = date('Y-m-d');

// Check if review exists
$stmt = $conn->prepare("SELECT * FROM review WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE review SET rating = ?, review_description = ?, review_date = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("issii", $rating, $review_text, $date, $user_id, $product_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO review (product_id, user_id, rating, review_description, review_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $product_id, $user_id, $rating, $review_text, $date);
    $stmt->execute();
}

header("Location: ../Modules/customer/order_history.php?review=success");
exit;
?>