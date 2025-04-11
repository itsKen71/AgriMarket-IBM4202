<?php
include 'database.php';
$db = new Database();
$conn = $db->conn;

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

try {
    $conn->begin_transaction();

    $user_id = $_SESSION['user_id'];
    $address = $data['address'];
    $final_amount = $data['final_amount'];
    $payment_method = $data['payment_method']['type'];
    $products = $data['products'];

    // Identify payment status based on payment method
    if ($payment_method == 'Cash On Delivery') {
        $payment_status = 'Pending';
    } else {
        $payment_status = 'Completed';
    }
    
    // 1. Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, price, order_date, delivery_date, shipping_address) 
                            VALUES (?, ?, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY), ?)");
    $stmt->bind_param("ids", $user_id, $final_amount, $address);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // 2. Insert into payment
    $stmt = $conn->prepare("INSERT INTO payment (order_id, user_id, total_amount, payment_method, payment_status, transaction_date) 
                        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP())");
    $stmt->bind_param("iidss", $order_id, $user_id, $final_amount, $payment_method, $payment_status);
    $stmt->execute();
    $stmt->close();

    // 3. Insert into customer_order_history
    $stmt = $conn->prepare("INSERT INTO customer_order_history (order_id, status, order_date) 
                            VALUES (?, 'Completed', CURRENT_DATE())");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // 4. Insert into shipment
    $tracking_number = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    $stmt = $conn->prepare("INSERT INTO shipment (order_id, tracking_number, status, update_timestamp, estimated_delivery_date) 
                            VALUES (?, ?, 'Pending', CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY))");
    $stmt->bind_param("is", $order_id, $tracking_number);
    $stmt->execute();
    $stmt->close();

    // 5. Insert into product_order
    $stmt = $conn->prepare("INSERT INTO product_order (product_id, order_id, quantity, sub_price, packaging, status) 
                            VALUES (?, ?, ?, ?, ?,'Completed')");

    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $quantity = $product['quantity'];
        $packaging = $product['packaging'];

        // Get current unit price
        $price_stmt = $conn->prepare("SELECT unit_price FROM product WHERE product_id = ?");
        $price_stmt->bind_param("i", $product_id);
        $price_stmt->execute();
        $result = $price_stmt->get_result();
        $row = $result->fetch_assoc();
        $unit_price = $row['unit_price'];
        $price_stmt->close();

        $sub_price = $unit_price * $quantity;

        $stmt->bind_param("iiids", $product_id, $order_id, $quantity, $sub_price, $packaging);
        $stmt->execute();
    }
    $stmt->close();

    // 6. Delete purchased items from cart
    if (isset($data['source']) && $data['source'] === 'cart') {
        $product_ids = array_column($products, 'product_id');
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
        $bind_params = array_merge([$user_id], $product_ids);
        
        $stmt->bind_param("i" . $types, ...$bind_params);
        $stmt->execute();
        $stmt->close();
    }

    // 7. Decrease items stock quantity
    $stmt = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);

    foreach ($products as $product) 
    {
        $product_id = $product['product_id'];
        $quantity = $product['quantity'];
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update stock for product ID: $product_id");
        }
    }

    $stmt->close();

        $conn->commit();

        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
?>
