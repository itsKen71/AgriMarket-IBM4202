<?php
session_start();
include 'database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$outOfStockItems = [];

foreach ($input['products'] as $item) {
    $productId = $item['product_id'];
    $quantity = $item['quantity'];
    
    // get product and stock info
    $query = "SELECT product_name, stock_quantity FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if ($quantity > $product['stock_quantity']) {
            $outOfStockItems[] = [
                'product_id' => $productId,
                'product_name' => $product['product_name'],
                'requested_quantity' => $quantity,
                'available_quantity' => $product['stock_quantity']
            ];
        }
    } else {
        $outOfStockItems[] = [
            'product_id' => $productId,
            'product_name' => 'Unknown Product',
            'requested_quantity' => $quantity,
            'available_quantity' => 0
        ];
    }
}

if (count($outOfStockItems) > 0) {
    $errorMessages = array_map(function($item) {
        return sprintf(
            "%s (Requested: %d, Available: %d)",
            htmlspecialchars($item['product_name']),
            $item['requested_quantity'],
            $item['available_quantity']
        );
    }, $outOfStockItems);
    
    $message = "The following items exceed available stock:<br>" . 
           implode("<br>", $errorMessages);
    
    echo json_encode([
        'success' => false,
        'message' => $message,
        'out_of_stock_items' => $outOfStockItems
    ]);
} else {
    echo json_encode(['success' => true]);
}
?>