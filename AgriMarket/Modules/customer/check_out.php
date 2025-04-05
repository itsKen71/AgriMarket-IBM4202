<?php
include '..\..\includes\database.php';


session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize variables to track the source
    $source = '';
    $selected_products = [];
    
    // Check if coming from Buy Now
    if (isset($_POST["product_id"]) && isset($_POST["quantity"])) {
        $source = 'buy_now';
        $selected_products = [
            [
                'product_id' => (int)$_POST["product_id"],
                'quantity' => (int)$_POST["quantity"]
            ]
        ];
    } 
    // Check if coming from Cart
    elseif (isset($_POST["selected_products"])) {
        $source = 'cart';
        $selected_products = json_decode($_POST["selected_products"], true);
        
        // Validate JSON data
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($selected_products)) {
            die("Invalid cart data.");
        }
        
        // Convert product IDs and quantities to integers
        foreach ($selected_products as &$product) {
            $product['product_id'] = (int)$product['product_id'];
            $product['quantity'] = (int)$product['quantity'];
        }
    } 
    else {
        die("No products selected.");
    }
    
    // Now fetch product data from database
    if (!empty($selected_products)) {
        // Extract product IDs
        $product_ids = array_column($selected_products, 'product_id');
        
        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        // Prepare SQL query
        $sql = "SELECT product_id, product_name, unit_price, product_image 
                FROM product 
                WHERE product_id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        
        // Bind parameters dynamically
        $types = str_repeat('i', count($product_ids));
        $stmt->bind_param($types, ...$product_ids);
        
        // Execute query
        if (!$stmt->execute()) {
            die("Query failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $products_from_db = [];
        
        // Store products in associative array with product_id as key
        while ($row = $result->fetch_assoc()) {
            $products_from_db[$row['product_id']] = $row;
        }
        
        $stmt->close();
        
        $total_amount = 0;
        $total_items = 0;

        // Enrich selected products with database data
        foreach ($selected_products as &$product) {
            $product_id = $product['product_id'];
            if (isset($products_from_db[$product_id])) {
                $product['name'] = $products_from_db[$product_id]['product_name'];
                $product['price'] = $products_from_db[$product_id]['unit_price'];
                $product['image'] = $products_from_db[$product_id]['product_image'];
                $product['subtotal'] = $product['price'] * $product['quantity'];

                $total_amount += $product['subtotal'];
                $total_items += $product['quantity'];
            } else {
                // Handle case where product doesn't exist
                die("Product ID $product_id not found.");
            }
        }
        
        // Now $selected_products contains all the information you need
        // You can use $source variable to know where it came from
        
        // Example output:
        echo "<h2>Order Source: " . htmlspecialchars($source) . "</h2>";
        echo "<pre>" . print_r($selected_products, true) . "</pre>";
        echo "<h2>Total Items: " . htmlspecialchars($total_items) . "</h2>";
        echo "<h2>Total Amount: $" . number_format($total_amount, 2) . "</h2>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Check Out</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/check_out.css">
</head>
<body class="check_out bg-light">
    <?php include '../../includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Shipping Address -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Enter your complete delivery address"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Products Summary -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-shopping-basket me-2"></i>Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Image</th>
                                        <th class="text-center">Unit Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($selected_products)) : ?>
                                    <?php foreach ($selected_products as &$product) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="text-center">
                                                <img src="../../<?php echo htmlspecialchars($product['image']); ?>" width="50" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            </td>
                                            <td class="text-center">$<?php echo number_format($product['price'], 2); ?></td>
                                            <td class="text-center"><?php echo $product['quantity']; ?></td>
                                            <td class="text-end">$<?php echo number_format($product['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No products selected</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h4>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="paymentAccordion">
                            <!-- Credit Card Option -->
                            <div class="accordion-item border mb-3">
                                <h2 class="accordion-header">
                                    <div class="form-check p-0">
                                        <input class="form-check-input position-static ms-3 mt-3" type="radio" name="paymentMethod" id="creditCard" value="credit_card" data-bs-toggle="collapse" data-bs-target="#creditCardForm" aria-expanded="true">
                                        <label class="accordion-button" for="creditCard">
                                            <span class="fw-bold">Credit Card</span>
                                        </label>
                                    </div>
                                </h2>
                                <div id="creditCardForm" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="cardName" class="form-label">Name on Card</label>
                                                <input type="text" class="form-control" id="cardName" placeholder="Full name as displayed on card">
                                            </div>
                                            <div class="col-12">
                                                <label for="cardNumber" class="form-label">Card Number</label>
                                                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="expMonth" class="form-label">Expiration Month</label>
                                                <select class="form-select" id="expMonth">
                                                    <?php for($i=1; $i<=12; $i++) : ?>
                                                        <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="expYear" class="form-label">Expiration Year</label>
                                                <select class="form-select" id="expYear">
                                                    <?php for($i=date('Y'); $i<=date('Y')+10; $i++) : ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="cvv" class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cvv" placeholder="123">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer Option -->
                            <div class="accordion-item border mb-3">
                                <h2 class="accordion-header">
                                    <div class="form-check p-0">
                                        <input class="form-check-input position-static ms-3 mt-3" type="radio" name="paymentMethod" id="bankTransfer" value="bank_transfer" data-bs-toggle="collapse" data-bs-target="#bankTransferForm" aria-expanded="false">
                                        <label class="accordion-button collapsed" for="bankTransfer">
                                            <span class="fw-bold">Bank Transfer</span>
                                        </label>
                                    </div>
                                </h2>
                                <div id="bankTransferForm" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="accountName" class="form-label">Account Holder Name</label>
                                                <input type="text" class="form-control" id="accountName">
                                            </div>
                                            <div class="col-12">
                                                <label for="bankName" class="form-label">Bank Name</label>
                                                <input type="text" class="form-control" id="bankName">
                                            </div>
                                            <div class="col-12">
                                                <label for="accountNumber" class="form-label">Account Number</label>
                                                <input type="text" class="form-control" id="accountNumber">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cash On Delivery Option -->
                            <div class="accordion-item border">
                                <h2 class="accordion-header">
                                    <div class="form-check p-0">
                                        <input class="form-check-input position-static ms-3 mt-3" type="radio" name="paymentMethod" id="cashOnDelivery" value="cash_on_delivery" data-bs-toggle="collapse" data-bs-target="#cashOnDeliveryInfo" aria-expanded="false">
                                        <label class="accordion-button collapsed" for="cashOnDelivery">
                                            <span class="fw-bold">Cash On Delivery</span>
                                        </label>
                                    </div>
                                </h2>
                                <div id="cashOnDeliveryInfo" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-0">Pay with cash upon delivery. Please ensure someone is available at the delivery address to receive the order and make payment.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4 position-sticky" style="top: 2rem;">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Total</h4>
                    </div>
                    <div class="card-body">
                        <!-- Discount Code -->
                        <div class="mb-3">
                            <label for="discountCode" class="form-label">Discount Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="discountCode" placeholder="Enter code">
                                <button class="btn btn-outline-success" type="button" id="applyDiscount">Apply</button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Order Summary -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <span><?php echo $total_items ?? 0; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total_amount ?? 0, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 discount-row" style="display: none !important;">
                            <span>Discount:</span>
                            <span class="discount-amount text-success">-$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2 fw-bold">
                            <span>Total:</span>
                            <span class="total-amount">$<?php echo number_format($total_amount ?? 0, 2); ?></span>
                        </div>
                        
                        <!-- Checkout Button -->
                        <div class="d-grid gap-2 mt-4">
                            <button id="checkoutBtn" class="btn btn-success btn-lg" type="button">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>