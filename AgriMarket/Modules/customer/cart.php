<?php
    session_start();
    include '..\..\includes\database.php';

    $user_id = 1;

    // 查询购物车获取 product_id 和 quantity
    $query = "SELECT product_id, quantity FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Cart Query Preparation Failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $quantity = $row['quantity']; // 获取购物车中对应的数量

        // 查询产品详情
        $query_product = "SELECT * FROM product WHERE product_id = ?";
        $stmt_product = $conn->prepare($query_product);

        if (!$stmt_product) {
            die("Product Query Preparation Failed: " . $conn->error);
        }

        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();

        if ($product = $result_product->fetch_assoc()) {
            $product['quantity'] = $quantity; // 将购物车的数量添加到产品数据里
            $products[] = $product;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
        $product_id = $_POST['product_id'];
        
        $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        
        if ($stmt_delete) {
            $stmt_delete->bind_param("ii", $user_id, $product_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // 重定向以避免重复提交
            header("Location: cart.php");
            exit();
        }
    }

    // 处理数量更新请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['new_quantity'];
    
    // 验证数量 (至少为1)
    $new_quantity = max(1, (int)$new_quantity);
    
    $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt_update = $conn->prepare($update_query);
    
    if ($stmt_update) {
        $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // 返回JSON响应
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'new_quantity' => $new_quantity,
            'subtotal' => number_format($_POST['unit_price'] * $new_quantity, 2)
        ]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Cart</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/cart.css">
    <style>
        .cart {
            background-color: #f8f9fa;
        }
        .price-color {
            color: #dc3545;
        }
        .quantity-control button {
            width: 30px;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .checkout-btn:hover {
            background-color: #c82333;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
            heg
        }
    </style>

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
                    <div class="fw-medium" style="width: 25%; margin-left: 135px;">Price</div>  <!-- 手动调整左边距 -->
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
                            <input type="checkbox" class="form-check-input">
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
                                <button type="submit" name="delete_item" class="delete-btn text-danger small mb-2 cursor-pointer border-0 bg-transparent">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </form>
                            <div class="similar-btn text-primary small cursor-pointer">
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
            <div class="total-section d-flex justify-content-between align-items-center p-4 bg-light rounded-bottom mt-3">
                <div class="d-flex align-items-center">
                <input type="checkbox" id="selectAll" class="form-check-input select-all">
                <label for="selectAll" class="ms-5 fw-medium px-1">Item</label>
                    <button class="btn btn-link text-danger p-0 ms-3 text-decoration-none">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3">Total (0 Items):</span>
                    <span class="price-color fs-4 fw-bold">RM0.00</span>
                    <button class="checkout-btn ms-3 btn btn-danger px-4 py-2 fw-medium">
                        Check Out <i class="fas fa-arrow-right ms-2"></i>
                    </button>
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
    // 为所有加减按钮添加事件监听
    document.querySelectorAll('.plus-btn, .minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const isPlus = this.classList.contains('plus-btn');
            const quantityDisplay = this.parentElement.querySelector('.quantity-display');
            const subtotalElement = this.closest('.cart-item').querySelector('.subtotal');
            const productId = this.dataset.productId;
            const unitPrice = parseFloat(this.dataset.unitPrice);
            
            let currentQuantity = parseInt(quantityDisplay.textContent);
            let newQuantity = isPlus ? currentQuantity + 1 : Math.max(1, currentQuantity - 1);
            
            // 立即更新UI (乐观更新)
            quantityDisplay.textContent = newQuantity;
            const newSubtotal = (unitPrice * newQuantity).toFixed(2);
            subtotalElement.textContent = 'RM' + newSubtotal;
            
            // 更新总价
            updateCartTotals();
            
            // 发送请求到服务器
            updateQuantityInDatabase(productId, newQuantity, unitPrice);
        });
    });
    
    // 更新数据库中的数量
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
                // 如果服务器更新失败，恢复UI
                alert('Failed to update quantity: ' + (data.error || 'Unknown error'));
                location.reload(); // 重新加载页面以同步状态
            }
            // 成功则不需要做任何事，因为我们已经乐观更新了
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
            location.reload();
        });
    }
    
    // 更新购物车总价和总数
    function updateCartTotals() {
        const items = document.querySelectorAll('.cart-item');
        let totalItems = 0;
        let totalPrice = 0;
        
        items.forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-display').textContent);
            const subtotalText = item.querySelector('.subtotal').textContent.replace('RM', '');
            const subtotal = parseFloat(subtotalText);
            
            totalItems += quantity;
            totalPrice += subtotal;
        });
        
        // 更新显示
        const totalItemsElement = document.querySelector('.total-section span:first-child');
        const totalPriceElement = document.querySelector('.price-color.fs-4');
        
        totalItemsElement.textContent = `Total (${totalItems} ${totalItems === 1 ? 'Item' : 'Items'}):`;
        totalPriceElement.textContent = `RM${totalPrice.toFixed(2)}`;
    }
    
    // 删除按钮的事件监听 (之前添加的代码)
    // ...
});
</script>