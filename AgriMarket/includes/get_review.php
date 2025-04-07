<?php
session_start();
include 'database.php';

$user_id = $_SESSION['user_id'] ?? null;
$product_id = $_GET['product_id'] ?? null;

if (!$user_id || !$product_id) {
    echo json_encode([]);
    exit;
}

$query = "SELECT rating, review_description FROM review WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($review = $result->fetch_assoc()) {
    echo json_encode($review);
} else {
    echo json_encode([]);
}
?>
