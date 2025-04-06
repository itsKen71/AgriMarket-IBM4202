<?php
include 'database.php';

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    $discountCode = trim($_POST['discount_code'] ?? '');
    $totalAmount = floatval($_POST['total_amount'] ?? 0);
    
    if (empty($discountCode)) {
        throw new Exception("Discount code is required");
    }
    
    $sql = "SELECT discount_percentage, min_amount_purchase 
            FROM discount 
            WHERE discount_code = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $discountCode);
    
    if (!$stmt->execute()) {
        throw new Exception("Query failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Get all available discount codes for error messages
        $allCodes = $conn->query("SELECT discount_code FROM discount");
        $codes = [];
        while ($row = $allCodes->fetch_assoc()) {
            $codes[] = $row['discount_code'];
        }
        throw new Exception("Invalid discount code.");
    }
    
    $discount = $result->fetch_assoc();
    
    if ($totalAmount < $discount['min_amount_purchase']) {
        throw new Exception("Minimum purchase of $" . 
            number_format($discount['min_amount_purchase'], 2) . 
            " required (your total: $" . number_format($totalAmount, 2) . ")");
    }
    
    echo json_encode([
        'success' => true,
        'discount_percentage' => $discount['discount_percentage'],
        'message' => 'Discount applied successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>