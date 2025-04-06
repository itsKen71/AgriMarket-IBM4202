<?php
include '..\..\includes\database.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../authentication/login.php");
        exit();
    }
    
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
    }
}

// get user address
$user_address = "";
$stmt = $conn->prepare("SELECT home_address FROM user WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $user_address = htmlspecialchars($user_data['home_address']);
}
$stmt->close();

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
    <link rel="stylesheet" href="../../css/check_out.css?v=<?= filemtime('../../css/check_out.css') ?>">
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
                        <textarea class="form-control" id="address" name="address" rows="3" required 
                                placeholder="Enter your complete delivery address"><?php echo $user_address; ?></textarea>
                    </div>
                    <!-- Add a checkbox to allow the user to modify the address -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="editAddress">
                        <label class="form-check-label" for="editAddress">Use Other Address</label>
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
                                        <th class="text-end">Packaging</th>
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
                                            <td class="text-center">RM<?php echo number_format($product['price'], 2); ?></td>
                                            <td class="text-center"><?php echo $product['quantity']; ?></td>
                                            <td class="text-center">
                                                <select name="protection_option[]" class="form-select">
                                                    <option value="Normal" selected>Normal</option>
                                                    <option value="More Protection">More Protection</option>
                                                </select>
                                            </td>
                                            <td class="text-end">RM<?php echo number_format($product['subtotal'], 2); ?></td>
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
                                        <label class="accordion-button collapsed" for="creditCard">
                                            <span class="fw-bold">Credit</span>
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
                                                <select class="form-select" id="bankName">
                                                    <option value="Affin Bank">Affin Bank</option>
                                                    <option value="Affin Islamic Bank">Affin Islamic Bank</option>
                                                    <option value="Alliance Bank">Alliance Bank</option>
                                                    <option value="Alliance Islamic Bank">Alliance Islamic Bank</option>
                                                    <option value="AmBank">AmBank</option>
                                                    <option value="AmIslamic Bank">AmIslamic Bank</option>
                                                    <option value="Bangkok Bank Malaysia">Bangkok Bank Malaysia</option>
                                                    <option value="Bank Islam Malaysia">Bank Islam Malaysia</option>
                                                    <option value="Bank Kerjasama Rakyat Malaysia">Bank Kerjasama Rakyat Malaysia</option>
                                                    <option value="Bank Muamalat Malaysia">Bank Muamalat Malaysia</option>
                                                    <option value="Bank of China Malaysia">Bank of China Malaysia</option>
                                                    <option value="Bank Pembangunan Malaysia">Bank Pembangunan Malaysia</option>
                                                    <option value="Bank Pertanian Malaysia (Agrobank)">Bank Pertanian Malaysia (Agrobank)</option>
                                                    <option value="Bank Rakyat">Bank Rakyat</option>
                                                    <option value="Bank Simpanan Nasional">Bank Simpanan Nasional</option>
                                                    <option value="BNP Paribas Malaysia">BNP Paribas Malaysia</option>
                                                    <option value="CIMB Bank">CIMB Bank</option>
                                                    <option value="CIMB Islamic Bank">CIMB Islamic Bank</option>
                                                    <option value="Citibank Malaysia">Citibank Malaysia</option>
                                                    <option value="Deutsche Bank Malaysia">Deutsche Bank Malaysia</option>
                                                    <option value="Export-Import Bank of Malaysia">Export-Import Bank of Malaysia</option>
                                                    <option value="Hong Leong Bank">Hong Leong Bank</option>
                                                    <option value="Hong Leong Islamic Bank">Hong Leong Islamic Bank</option>
                                                    <option value="HSBC Bank Malaysia">HSBC Bank Malaysia</option>
                                                    <option value="Industrial and Commercial Bank of China Malaysia">Industrial and Commercial Bank of China Malaysia</option>
                                                    <option value="J.P. Morgan Chase Bank Malaysia">J.P. Morgan Chase Bank Malaysia</option>
                                                    <option value="Maybank">Maybank</option>
                                                    <option value="Maybank Islamic">Maybank Islamic</option>
                                                    <option value="OCBC Bank Malaysia">OCBC Bank Malaysia</option>
                                                    <option value="Public Bank">Public Bank</option>
                                                    <option value="Public Islamic Bank">Public Islamic Bank</option>
                                                    <option value="RHB Bank">RHB Bank</option>
                                                    <option value="RHB Islamic Bank">RHB Islamic Bank</option>
                                                    <option value="SME Bank">SME Bank</option>
                                                    <option value="Standard Chartered Bank Malaysia">Standard Chartered Bank Malaysia</option>
                                                    <option value="The Bank of Tokyo-Mitsubishi UFJ Malaysia">The Bank of Tokyo-Mitsubishi UFJ Malaysia</option>
                                                    <option value="United Overseas Bank Malaysia">United Overseas Bank Malaysia</option>
                                                </select>
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
                            <span>RM<?php echo number_format($total_amount ?? 0, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 discount-row" style="display: none !important;">
                            <span>Discount:</span>
                            <span class="discount-amount text-success">-RM0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>RM0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2 fw-bold">
                            <span>Total:</span>
                            <span class="total-amount">RM<?php echo number_format($total_amount ?? 0, 2); ?></span>
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

    <div id="popup" class="popup">
        <h4 id="popup-message">Discount applied successfully!</h4>
        <button onclick="closePopup()">OK</button>
    </div>

    <?php include '../../includes/footer.php';?>

    <input type="hidden" name="applied_discount_code" id="appliedDiscountCode" value="">
    <input type="hidden" name="discount_percentage" id="discountPercentage" value="0">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

function showPopup(message) {
    const popup = document.getElementById('popup');
    const popupMessage = document.getElementById('popup-message');
    
    popupMessage.textContent = message;
    popup.classList.add('show');
    
    // Auto close after 3 seconds (optional)
    setTimeout(() => {
        closePopup();
    }, 3000);
}

function closePopup() {
    const popup = document.getElementById('popup');
    popup.classList.remove('show');
}

    document.getElementById('applyDiscount').addEventListener('click', function() {
    var discountCode = document.getElementById('discountCode').value.trim();
    var totalAmount = <?php echo $total_amount ?? 0; ?>;
    
    if (discountCode === '') {
        alert('Please enter a discount code');
        return;
    }
    
    // Make AJAX call to check discount code
    fetch('../../includes/check_discount.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `discount_code=${encodeURIComponent(discountCode)}&total_amount=${totalAmount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Apply discount
            const discountAmount = totalAmount * (data.discount_percentage / 100);
            const newTotal = totalAmount - discountAmount;
            
            // Update UI
            document.querySelector('.discount-row').style.display = 'flex';
            document.querySelector('.discount-amount').textContent = `-$${discountAmount.toFixed(2)}`;
            document.querySelector('.total-amount').textContent = `$${newTotal.toFixed(2)}`;
            
            // Store discount info in hidden fields for form submission
            document.getElementById('discountCode').setAttribute('data-valid', 'true');
            document.getElementById('discountCode').setAttribute('data-percentage', data.discount_percentage);
            
            showPopup('Discount applied successfully!');
        } else {
            showPopup('Invalid discount code | No meet the requirement');
            document.querySelector('.discount-row').style.display = 'none';
            document.querySelector('.total-amount').textContent = `$${totalAmount.toFixed(2)}`;
            document.getElementById('discountCode').setAttribute('data-valid', 'false');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while applying discount');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const addressField = document.getElementById('address');
    const editCheckbox = document.getElementById('editAddress');
    
    // Initially disable editing (if address exists)
    if (addressField.value.trim() !== '') {
        addressField.readOnly = true;
    }
    
    // change to edit status
    editCheckbox.addEventListener('change', function() {
        addressField.readOnly = !this.checked;
        if (this.checked) {
            addressField.focus();
        } else {
            // if cancel change address will replace by address found in database
            addressField.value = '<?php echo $user_address; ?>';
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
    
    // Add change event to all payment methods
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            updateRequiredFields();
        });
    });

    function updateRequiredFields() {
        // First, remove required from all payment method fields
        const allPaymentInputs = document.querySelectorAll(`
            #creditCardForm input, #creditCardForm select,
            #bankTransferForm input, #bankTransferForm select
        `);
        
        allPaymentInputs.forEach(input => {
            input.required = false;
            input.removeAttribute('aria-required');
        });

        // Get the currently selected payment method
        const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked');
        
        if (!selectedMethod) return;

        // Set required fields based on selected method
        let targetInputs = [];
        
        if (selectedMethod.id === 'creditCard') {
            targetInputs = document.querySelectorAll(`
                #cardName, #cardNumber, #expMonth, #expYear, #cvv
            `);
        } 
        else if (selectedMethod.id === 'bankTransfer') {
            targetInputs = document.querySelectorAll(`
                #accountName, #bankName, #accountNumber
            `);
        }
        // Cash on delivery doesn't require any additional fields
        
        // Set required attribute on the relevant fields
        targetInputs.forEach(input => {
            input.required = true;
            input.setAttribute('aria-required', 'true');
        });
    }

    // Initialize on page load
    updateRequiredFields();

    // Also update when accordion expands/collapses (in case user manually toggles)
    const paymentAccordion = document.getElementById('paymentAccordion');
    if (paymentAccordion) {
        paymentAccordion.addEventListener('shown.bs.collapse', updateRequiredFields);
        paymentAccordion.addEventListener('hidden.bs.collapse', updateRequiredFields);
    }
});

document.getElementById('checkoutBtn').addEventListener('click', function() {
    // Validate address
    const address = document.getElementById('address').value.trim();
    if (!address) {
        showPopup('Please enter a delivery address');
        return;
    }

    // Validate payment method
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    if (!paymentMethod) {
        showPopup('Please select a payment method');
        return;
    }

    // Validate payment details based on selected method
    let paymentValid = true;
    let paymentDetails = {};
    
    if (paymentMethod.id === 'creditCard') {
        const cardName = document.getElementById('cardName').value.trim();
        const cardNumber = document.getElementById('cardNumber').value.trim();
        const cvv = document.getElementById('cvv').value.trim();
        
        if (!cardName || !cardNumber || !cvv) {
            showPopup('Please fill in all credit card details');
            paymentValid = false;
        } else {
            paymentDetails = {
                type: 'Credit Card',
                cardName: cardName,
                cardNumber: cardNumber,
                expMonth: document.getElementById('expMonth').value,
                expYear: document.getElementById('expYear').value,
                cvv: cvv
            };
        }
    } 
    else if (paymentMethod.id === 'bankTransfer') {
        const accountName = document.getElementById('accountName').value.trim();
        const bankName = document.getElementById('bankName').value.trim();
        const accountNumber = document.getElementById('accountNumber').value.trim();
        
        if (!accountName || !bankName || !accountNumber) {
            showPopup('Please fill in all bank transfer details');
            paymentValid = false;
        } else {
            paymentDetails = {
                type: 'Bank Transfer',
                accountName: accountName,
                bankName: bankName,
                accountNumber: accountNumber
            };
        }
    }
    else if (paymentMethod.id === 'cashOnDelivery') {
        paymentDetails = {
            type: 'Cash On Delivery'
        };
    }

    if (!paymentValid) return;

    // Collect product data - FIXED THIS SECTION
    const products = [];
    const productRows = document.querySelectorAll('tbody tr');
    
    // Get the PHP product data as JavaScript array
    const phpProducts = <?php echo json_encode($selected_products ?? []); ?>;
    
    productRows.forEach((row, index) => {
        // Skip if it's the "no products" row
        if (row.querySelector('td[colspan]')) return;
        
        // Get the corresponding product from PHP data
        if (index < phpProducts.length) {
            const product = phpProducts[index];
            const protectionOption = row.querySelector('select[name="protection_option[]"]').value;
            
            products.push({
                product_id: product.product_id,
                quantity: product.quantity,
                packaging: protectionOption
            });
        }
    });

    // Collect discount data if applied
    const discountCode = document.getElementById('discountCode').value;
    const discountPercentage = document.getElementById('discountCode').getAttribute('data-percentage') || 0;
    const discountAmount = <?php echo $total_amount ?? 0; ?> * (discountPercentage / 100);

    // Prepare order data
    const orderData = {
        user_id: <?php echo $user_id ?? 'null'; ?>,
        address: address,
        products: products,
        payment_method: paymentDetails,
        total_amount: <?php echo $total_amount ?? 0; ?>,
        discount_code: discountCode,
        discount_amount: discountAmount,
        final_amount: <?php echo $total_amount ?? 0; ?> - discountAmount,
        source: '<?php echo $source ?? ""; ?>' // Add the source information
    };

    // Submit order via AJAX
    fetch('../../includes/process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to order confirmation page with order ID
            window.location.href = 'main_page.php?';
        } else {
            showPopup('Order failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showPopup('An error occurred while processing your order');
    });
});
</script>
</body>

</html>