<?php
session_start();
include '..\..\includes\database.php'; // Include the database connection file
include '..\..\includes\cart_functions.php'; // Include cart-related functions

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'] ?? null;

// Redirect to the login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

// Fetch the user's cart items
$products = getUserCart($conn, $user_id);

// Handle deletion of a cart item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Missing product_id']);
        exit();
    }

    $product_id = (int) $_POST['product_id']; // Get the product ID to delete
    $response = deleteCartItem($conn, $user_id, $product_id); // Call the function to delete the item

    header('Content-Type: application/json');
    echo json_encode($response); // Return the response as JSON
    exit();
}

// Handle updating the quantity of a cart item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id']; // Get the product ID
    $new_quantity = $_POST['new_quantity']; // Get the new quantity
    $unit_price = $_POST['unit_price']; // Get the unit price

    $response = updateCartQuantity($conn, $user_id, $product_id, $new_quantity, $unit_price); // Update the quantity

    header('Content-Type: application/json');
    echo json_encode($response); // Return the response as JSON
    exit();
}

// Fetch all products for comparison or other operations
$allProducts = [];
$allProductsQuery = "SELECT p.* FROM product p"; // Query to fetch all products
$allProductsResult = $conn->query($allProductsQuery);
while ($row = $allProductsResult->fetch_assoc()) {
    $row['average_rating'] = getAverageRating($conn, $row['product_id']); // Calculate the average rating for each product
    $allProducts[$row['product_id']] = $row; // Store the product data
}

// Function to display star ratings
function displayStars($rating) {
    $fullStars = floor($rating); // Calculate the number of full stars
    $hasHalfStar = ($rating - $fullStars) >= 0.5; // Check if there is a half star
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0); // Calculate the number of empty stars
    $output = '';
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $output .= '<i class="fas fa-star"></i>';
    }
    // Half star
    if ($hasHalfStar) {
        $output .= '<i class="fas fa-star-half-alt"></i>';
    }
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= '<i class="far fa-star"></i>';
    }
    return $output;
}

// Handle switching products in the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['switch_product'])) {
    $current_product_id = $_POST['current_product_id']; // Get the current product ID
    $compare_product_id = $_POST['compare_product_id']; // Get the product ID to switch with

    $response = switchCartProduct($conn, $user_id, $current_product_id, $compare_product_id); // Perform the switch

    header('Content-Type: application/json');
    echo json_encode($response); // Return the response as JSON
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Cart</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/cart.css?v=<?= filemtime('../../css/cart.css') ?>">
</head>
<body class="cart">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->
        <h1 class="h1 mb-4">Shopping Cart</h1>
        <div class="cart-container bg-white rounded-3 shadow-sm p-4">
            <!-- Cart Header -->
            <div class="cart-header d-flex justify-content-between align-items-center py-3 px-3 bg-light rounded-top">
                <div class="d-flex align-items-center">
                    <label for="selectAll" class="ms-5 fw-medium px-4">Item</label>
                </div>
                <div class="d-flex" style="width: 60%;">
                    <div class="fw-medium" style="width: 25%; margin-left: 135px;">Price</div>
                    <div class="fw-medium" style="width: 20%; margin-right: 25px;">Quantity</div>
                    <div class="fw-medium" style="width: 20%; margin-left: 25px; margin-right: 45px;">Subtotal</div>
                    <div class="fw-medium" style="width: 10%; margin-left: px;">Operation</div>
                </div>
            </div>
 
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <div class="cart-item border-bottom py-4 px-3">
                    <div class="d-flex w-100 align-items-center">
                        <div style="width: 5%;">
                            <input type="checkbox" class="form-check-input item-checkbox" data-product-id="<?= $row['product_id'] ?>" data-unit-price="<?= $row['unit_price'] ?>">
                        </div>
                        <div style="width:15%;" class="pe-3">
                            <img src="../../<?php echo htmlspecialchars($row['product_image']); ?>" 
                            class="product-image mb-3"
                            style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                        </div>
                        <div style="width: 25%;">
                            <div class="fw-medium"><?= htmlspecialchars($row['product_name']); ?></div>
                        </div>
                        <div style="width: 15%;" class="text-center fw-medium">
                            <div class="fw-medium">RM<?= number_format($row['unit_price'], 2); ?></div>
                        </div>
                        <div style="width: 15%;" class="text-center">
                            <div class="quantity-control mx-auto d-flex align-items-center justify-content-center">
                                <button class="btn btn-outline-secondary btn-sm px-3 minus-btn" 
                                        data-product-id="<?= $row['product_id'] ?>" 
                                        data-unit-price="<?= $row['unit_price'] ?>">−</button>
                                <span class="mx-3 quantity-display"><?= htmlspecialchars($row['quantity']) ?></span>
                                <button class="btn btn-outline-secondary btn-sm px-3 plus-btn" 
                                        data-product-id="<?= $row['product_id'] ?>" 
                                        data-unit-price="<?= $row['unit_price'] ?>">+</button>
                            </div>
                        </div>
                        <div style="width: 15%;" class="text-center price-color fw-bold subtotal">
                            RM<?= number_format($row['unit_price'] * $row['quantity'], 2) ?>
                        </div>
                        <div style="width: 10%;" class="text-end">
                            <form method="POST" action="cart.php" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <button class="delete-btn text-danger small mb-2 cursor-pointer border-0 bg-transparent" 
                                        data-product-id="<?= $row['product_id'] ?>">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </form>
                            <div class="similar-btn text-primary small cursor-pointer" 
                                data-product-id="<?= $row['product_id']; ?>">
                                <i class="fas fa-random me-1"></i> Compare
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>No item in cart</p>
                </div>
            <?php endif; ?>
    
            <!-- Total Section -->
            <div class="d-flex fixed-bottom container px-4 justify-content-between align-items-center p-4 bg-light rounded-bottom mt-3">
                <div class="d-flex align-items-center">
                <input type="checkbox" id="selectAll2" class="form-check-input select-all">
                <label for="selectAll2" class="ms-2 fw-medium">Select All</label>
                    <button class="delete-btn btn btn-link text-danger p-0 ms-3 text-decoration-none">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total (0 Items):</span>
                    <span class="price-color fs-4 fw-bold">RM0.00</span>
                    <form id="checkoutForm" method="POST" action="check_out.php">
                    <input type="hidden" name="selected_products" id="selectedProducts">
                    <button type="submit" class="checkout-btn ms-3 btn btn-danger px-4 py-2 fw-medium">
                        Check Out <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Compare Modal -->
    <div class="modal fade" id="compareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header text-white" style="background-color: #c9d8b6;" >
            <h5 class="modal-title">Product Comparison</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="compareForm">
            <div class="row g-4">
                <!-- current product -->
                <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                    <h6 class="mb-0">Selected Product</h6>
                    </div>
                    <div class="card-body" id="currentProduct">
                        <img src="" class="img-fluid rounded mb-3" id="currentProductImage" style="max-height: 200px;">
                        <h4 id="currentProductName"></h4>
                        <div class="text-muted mb-2" id="currentProductCategory"></div>
                        <div class="h5 text-danger mb-3" id="currentProductPrice"></div>
                        <div class="mb-3" id="currentProductDescription"></div>
                        <div class="mb-3">
                            <span class="text-muted">Sold Quantity: </span>
                            <span id="currentProductSoldQuantity"></span>
                        </div>
                        <div id="currentProductRating" class="mb-2"></div>
                    </div>
                </div>
                </div>
                
                <!-- Comapre Product -->
                <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                    <h6 class="mb-0">Compare With</h6>
                    </div>
                    <div class="card-body">
                    <div class="mb-3">
                        <label for="compareProductSelect" class="form-label">Select Product</label>
                        <select class="form-select" id="compareProductSelect">
                        <option value="">-- Select a product --</option>
                        <?php 
                        // get all data query
                        $allProductsQuery = "SELECT product_id, product_name FROM product";
                        $allProductsResult = $conn->query($allProductsQuery);
                        while ($product = $allProductsResult->fetch_assoc()): 
                        ?>
                            <option value="<?= $product['product_id'] ?>">
                            <?= htmlspecialchars($product['product_name']) ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div id="compareProductContainer" style="display: none;">
                        <img src="" class="img-fluid rounded mb-3" id="compareProductImage" style="max-height: 200px;">
                        <h4 id="compareProductName"></h4>
                        <div class="text-muted mb-2" id="compareProductCategory"></div>
                        <div class="h5 text-danger mb-3" id="compareProductPrice"></div>
                        <div class="mb-3" id="compareProductDescription"></div>
                        <div class="mb-3">
                            <span class="text-muted">Sold Quantity: </span>
                            <span id="compareProductSoldQuantity"></span>
                        </div>
                        <div id="compareProductRating" class="mb-2 d-flex align-items-center"></div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </form>
        </div>
        <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="switchProductsBtn">
        <i class="fas fa-random me-1"></i> Switch Product
    </button>
</div>
        </div>
    </div>
    </div>

    <!-- warning message -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-warning text-white">
            <h5 class="modal-title">Notification</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
            <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
            <p id="alertMessage">Please select at least one product.</p>
        </div>
        <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-warning text-white" data-bs-dismiss="modal">OK</button>
        </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // add event listeners for all plus and minus buttons
        document.querySelectorAll('.plus-btn, .minus-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const isPlus = this.classList.contains('plus-btn');
                const quantityDisplay = this.parentElement.querySelector('.quantity-display');
                const subtotalElement = this.closest('.cart-item').querySelector('.subtotal');
                const productId = this.dataset.productId;
                const unitPrice = parseFloat(this.dataset.unitPrice);
                
                let currentQuantity = parseInt(quantityDisplay.textContent);
                let newQuantity = isPlus ? currentQuantity + 1 : Math.max(1, currentQuantity - 1);
                
                // update UI
                quantityDisplay.textContent = newQuantity;
                const newSubtotal = (unitPrice * newQuantity).toFixed(2);
                subtotalElement.textContent = 'RM' + newSubtotal;
                
                // update total price
                updateCartTotals();
                
                // update quantity
                updateQuantityInDatabase(productId, newQuantity, unitPrice);
            });
        });
    
    // update database product quantity
    function updateQuantityInDatabase(productId, newQuantity, unitPrice) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `update_quantity=1&product_id=${productId}&new_quantity=${newQuantity}&unit_price=${unitPrice}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // if server update fail, refresh UI
                alert('Failed to update quantity: ' + (data.error || 'Unknown error'));
                location.reload(); // reload page
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
            location.reload();
        });
    }
    
    //select all and no select all function
    document.querySelectorAll('.select-all').forEach(selectAll => {
        selectAll.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateCartTotals(); //update total price
        });
    });

    // check all selected status when single item selection changes
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // check if the selected checkbox state needs to be updated
            const allChecked = [...document.querySelectorAll('.item-checkbox')].every(cb => cb.checked);
            document.querySelectorAll('.select-all').forEach(selectAll => {
                selectAll.checked = allChecked;
            });
            updateCartTotals(); // update total price
        });
    });

    // update cart total price and total item being choose
    function updateCartTotals() {
    const checkedItems = document.querySelectorAll('.item-checkbox:checked');
    let totalItems = 0;
    let totalPrice = 0;
    
    checkedItems.forEach(checkbox => {
        const cartItem = checkbox.closest('.cart-item');
        const quantity = parseInt(cartItem.querySelector('.quantity-display').textContent);
        const unitPrice = parseFloat(checkbox.dataset.unitPrice);
        
        totalItems += quantity;
        totalPrice += unitPrice * quantity;
    });
    
    // update selectors to match new fixed bottom bar
    const totalItemsElement = document.querySelector('.fixed-bottom .me-3'); // 选择"Total (x Items):"元素
    const totalPriceElement = document.querySelector('.fixed-bottom .price-color.fs-4'); // 选择总价元素
    
    // update display
    if (totalItemsElement && totalPriceElement) {
        totalItemsElement.textContent = `Total (${totalItems} ${totalItems === 1 ? 'Item' : 'Items'}):`;
        totalPriceElement.textContent = `RM${totalPrice.toFixed(2)}`;
        
        // if no item is selected
        if (checkedItems.length === 0) {
            totalItemsElement.textContent = 'Total (0 Items):';
            totalPriceElement.textContent = 'RM0.00';
        }
    }
}
    
    // delete selected product
    // handle all delete button click events
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            let productIds = [];
            
            // determine whether to delete in batches or individually
            if (this.classList.contains('btn-link')) {
                // batch delete - get all selected product IDs
                document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
                    productIds.push(checkbox.dataset.productId);
                });
                
                if (productIds.length === 0) {
                    showToastMessage('Please select items to delete');
                    return;
                }
            } else {
            // single deletion - get the ID of the current product
                productIds.push(this.dataset.productId);
            }
            
            // execute delete operation
            deleteItems(productIds);
        });
    });

    // unified delete function
    function deleteItems(productIds) {
    const deletePromises = productIds.map(productId => {
        return fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_item=1&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log(`Response for ${productId}:`, data); // Debug Information
            return data;
        });
    });

    Promise.all(deletePromises)
        .then(results => {
            console.log("All delete responses:", results); // Debug Information
            
            const allSuccess = results.every(result => result.success);
            
            if (allSuccess) {
                productIds.forEach(productId => {
                    const item = document.querySelector(`[data-product-id="${productId}"]`).closest('.cart-item');
                    if (item) {
                        item.style.transition = 'all 0.3s ease';
                        item.style.opacity = '0';
                        item.style.height = '0';
                        item.style.padding = '0';
                        item.style.margin = '0';
                        setTimeout(() => {
                            item.remove();
                            updateCartTotals();
                        }, 300);
                    }
                });

                showToastMessage(productIds.length > 1 
                    ? `${productIds.length} items deleted` 
                    : 'Item deleted');
            } else {
                showToastMessage('Some items could not be deleted');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showToastMessage('Error occurred while deleting items');
            location.reload();
        });
}

    // show toast
    function showToastMessage(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.padding = '10px 20px';
        toast.style.backgroundColor = '#333';
        toast.style.color = '#fff';
        toast.style.borderRadius = '5px';
        toast.style.zIndex = '9999';
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }

    // initial compare btn
    document.querySelectorAll('.similar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            openCompareModal(productId);
        });
    });


    // open form
    function openCompareModal(productId) {
        // load current product info
        loadCurrentProduct(productId);
        
        // reset compare product
        document.getElementById('compareProductSelect').value = '';
        document.getElementById('compareProductContainer').style.display = 'none';
        
        // display form
        const compareModal = new bootstrap.Modal('#compareModal');
        compareModal.show();
    }

    // load current product info
    function loadCurrentProduct(productId) {
        const productItem = document.querySelector(`[data-product-id="${productId}"]`).closest('.cart-item');
        
        if (productItem) {
            document.getElementById('currentProductName').textContent = 
                productItem.querySelector('.fw-medium').textContent;
            document.getElementById('currentProductImage').src = 
                productItem.querySelector('.product-image').src;
            document.getElementById('currentProductPrice').textContent = 
                productItem.querySelector('.text-center.fw-medium .fw-medium').textContent;

            // generate complete product information from data generated by PHP
            const allProducts = <?php echo json_encode($allProducts); ?>;
            
            const currentProduct = allProducts[productId];
            if (currentProduct) {
                document.getElementById('currentProductSoldQuantity').textContent = 
                    currentProduct.sold_quantity || '0';
                document.getElementById('currentProductDescription').textContent = 
                    currentProduct.description || '-';

                // rating
                document.getElementById('currentProductRating').innerHTML = generateStars(currentProduct.average_rating);
            }
        }
    }

    //generate rating 
    function generateStars(rating) {
        let fullStars = Math.floor(rating);
        let hasHalfStar = (rating - fullStars) >= 0.5;
        let emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        let stars = '';

        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="fas fa-star text-warning"></i>';
        }
        if (hasHalfStar) {
            stars += '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="far fa-star text-warning"></i>';
        }

        //rating score
        return `<span class="me-2 fw-bold">${rating.toFixed(1)}</span>${stars}`;
    }

    // product select incident
    document.getElementById('compareProductSelect').addEventListener('change', function() {
        const selectedProductId = this.value;
        
        if (selectedProductId) {
            // generate complete product information from data generated by PHP
            const allProducts = <?php echo json_encode($allProducts ?? []); ?>;
            const product = allProducts[selectedProductId];
            
            if (product) {
                document.getElementById('compareProductContainer').style.display = 'block';
                document.getElementById('compareProductName').textContent = product.product_name;
                document.getElementById('compareProductImage').src = '../../' + product.product_image;
                document.getElementById('compareProductPrice').textContent = 'RM' + parseFloat(product.unit_price).toFixed(2);
                document.getElementById('compareProductDescription').textContent = product.description || 'No description available';
                
                // update sold quantity
                document.getElementById('compareProductSoldQuantity').textContent = 
                    product.sold_quantity || '0';

                // rating
                document.getElementById('compareProductRating').innerHTML = generateStars(product.average_rating);
            }
        } else {
            document.getElementById('compareProductContainer').style.display = 'none';
        }
    });


    document.querySelector('.checkout-btn').addEventListener('click', async function (e) {
    e.preventDefault();  // prevent default submit
    
    let selected = [];
    let quantities = [];
    let validationPassed = true;
    let errorMessage = "";
    
    // Get selected products and their quantities
    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const productId = checkbox.getAttribute('data-product-id');
        const quantity = parseInt(checkbox.closest('.cart-item').querySelector('.quantity-display').textContent);
        selected.push(productId);
        quantities.push(quantity);
    });

    if (selected.length === 0) {
        // display error
        const alertModal = new bootstrap.Modal('#alertModal');
        document.getElementById('alertMessage').textContent = "Please select at least one product.";
        alertModal.show();
        return;
    }

    // Validate stock quantities before proceeding
    try {
    const response = await fetch('../../includes/check_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            products: selected.map((id, index) => ({
                product_id: id,
                quantity: quantities[index]
            }))
        })
    });
    
    const data = await response.json();
    
    if (!data.success) {
        validationPassed = false;
        errorMessage = data.message || "Some items exceed available stock";
       
    }
    } catch (error) {
        console.error('Error:', error);
        validationPassed = false;
        errorMessage = "Error checking stock availability";
    }

    if (!validationPassed) {
        const alertModal = new bootstrap.Modal('#alertModal');
        document.getElementById('alertMessage').innerHTML = errorMessage;
        alertModal.show();
        return;
    }


    // If validation passed, proceed with checkout
    document.getElementById('selectedProducts').value = JSON.stringify(
        selected.map((id, index) => ({
            product_id: id,
            quantity: quantities[index]
        }))
    );
    document.getElementById('checkoutForm').submit();
});

    // switch product
    document.getElementById('switchProductsBtn').addEventListener('click', async function() {
        const activeCompareBtn = document.querySelector('.similar-btn.active');
        if (!activeCompareBtn) {
            showToastMessage('Please select a product to compare first');
            return;
        }

        const currentProductId = activeCompareBtn.dataset.productId;
        const compareProductId = document.getElementById('compareProductSelect').value;

        if (!compareProductId) {
            showToastMessage('Please select a product to compare with');
            return;
        }

        try {
            // send switch request
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `switch_product=1&current_product_id=${currentProductId}&compare_product_id=${compareProductId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // redirection to cart.php
                window.location.href = 'cart.php';
            } else {
                throw new Error(data.error || 'Failed to switch products');
            }
        } catch (error) {
            console.error('Error:', error);
            showToastMessage(error.message);
        }
    });

    // make sure compare btn is active
    document.querySelectorAll('.similar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.similar-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>