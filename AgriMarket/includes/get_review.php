<?php
session_start();
include 'database.php';

$productId = $_GET['product_id'];
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT rating, review_description FROM review WHERE product_id = ? AND user_id = ?");
$stmt->bind_param("ii", $productId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([]);
}
?>
