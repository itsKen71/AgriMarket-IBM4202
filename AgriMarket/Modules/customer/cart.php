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

    // 在cart.php中
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $product_id = $_POST['product_id'];
    
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    
    if ($stmt_delete) {
        $stmt_delete->bind_param("ii", $user_id, $product_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error']);
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

// 在查询产品详情前添加这个函数
function getAverageRating($conn, $productId) {
    $query = "SELECT AVG(rating) as average_rating FROM review WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['average_rating'] ?? 0, 1); // 四舍五入到1位小数
}

// 修改获取所有产品的查询
$allProducts = [];
$allProductsQuery = "SELECT p.* FROM product p";
$allProductsResult = $conn->query($allProductsQuery);
while ($row = $allProductsResult->fetch_assoc()) {
    $row['average_rating'] = getAverageRating($conn, $row['product_id']);
    $allProducts[$row['product_id']] = $row;
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
        .total-section {
            position: fixed;
            bottom: 0;
            left: 50%; /* 居中定位 */
            transform: translateX(-50%); /* 精确居中 */
            width: 100%; /* 初始宽度 */
            max-width: 100%; /* 防止溢出 */
            z-index: 1000;
            border-radius: 0 !important;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 !important;
            background-color: #f8f9fa;
        }

        /* 限制宽度与.container相同 */
        @media (min-width: 576px) {
            .total-section {
                max-width: 540px;
            }
        }
        @media (min-width: 768px) {
            .total-section {
                max-width: 720px;
            }
        }
        @media (min-width: 992px) {
            .total-section {
                max-width: 960px;
            }
        }
        @media (min-width: 1200px) {
            .total-section {
                max-width: 1140px;
            }
        }
        @media (min-width: 1400px) {
            .total-section {
                max-width: 1320px;
            }
        }
        
        .container.mt-5 {
            padding-bottom: 65px;
        }

        /* 比较模态框样式 */
#compareModal .modal-dialog {
    max-width: 90%;
}

.compare-product-image {
    max-height: 200px;
    width: auto;
    margin: 0 auto;
    display: block;
}

/* 卡片样式 */
#compareModal .card {
    height: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#compareModal .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* 响应式调整 */
@media (max-width: 768px) {
    #compareModal .modal-dialog {
        margin: 0.5rem auto;
    }
    
    #compareModal .row {
        flex-direction: column;
    }
    
    #compareModal .col-md-6 {
        margin-bottom: 1rem;
    }
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
            <div class="total-section d-flex justify-content-between align-items-center p-4 bg-light rounded-bottom mt-3">
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
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Product Comparison</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="compareForm">
            <div class="row g-4">
                <!-- 当前商品 -->
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

                    </div>
                </div>
                </div>
                
                <!-- 对比商品 -->
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
                        // 获取所有产品列表
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
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <!-- 提示模态框 -->
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
    
    // 全选/取消全选功能
document.querySelectorAll('.select-all').forEach(selectAll => {
    selectAll.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        updateCartTotals(); // 更新总价
    });
});

// 单个商品选择变化时检查全选状态
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // 检查是否需要更新全选复选框状态
        const allChecked = [...document.querySelectorAll('.item-checkbox')].every(cb => cb.checked);
        document.querySelectorAll('.select-all').forEach(selectAll => {
            selectAll.checked = allChecked;
        });
        updateCartTotals(); // 更新总价
    });
});

// 更新购物车总价和总数（修改现有函数）
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
    
    // 更新显示
    const totalItemsElement = document.querySelector('.total-section span:first-child');
    const totalPriceElement = document.querySelector('.price-color.fs-4');
    
    totalItemsElement.textContent = `Total (${totalItems} ${totalItems === 1 ? 'Item' : 'Items'}):`;
    totalPriceElement.textContent = `RM${totalPrice.toFixed(2)}`;
    
    // 如果没有选中任何商品，显示0
    if (checkedItems.length === 0) {
        totalItemsElement.textContent = 'Total (0 Items):';
        totalPriceElement.textContent = 'RM0.00';
    }
}
    
// 删除选中商品
// 处理所有删除按钮点击事件
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        let productIds = [];
        
        // 判断是批量删除还是单个删除
        if (this.classList.contains('btn-link')) {
            // 批量删除 - 获取所有选中的商品ID
            document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
                productIds.push(checkbox.dataset.productId);
            });
            
            if (productIds.length === 0) {
                showToastMessage('Please select items to delete');
                return;
            }
        } else {
            // 单个删除 - 获取当前商品的ID
            productIds.push(this.dataset.productId);
        }
        
        // 执行删除操作
        deleteItems(productIds);
    });
});

// 统一的删除函数
function deleteItems(productIds) {
    // 创建删除请求数组
    const deletePromises = productIds.map(productId => {
        return fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_item=1&product_id=${productId}`
        }).then(response => response.json());
    });
    
    // 执行所有删除请求
    Promise.all(deletePromises)
        .then(results => {
            const allSuccess = results.every(result => result.success);
            
            if (allSuccess) {
                // 从DOM中移除已删除的商品
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
                            // 确保在DOM完全移除后更新总计
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
            console.error('Error:', error);
            showToastMessage('Error occurred while deleting items');
            location.reload();
        });
}

// 显示Toast消息的函数
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
    toast.style.zIndex = '1000';
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

// 初始化比较按钮
document.querySelectorAll('.similar-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        openCompareModal(productId);
    });
});


// 打开比较模态框
function openCompareModal(productId) {
    // 加载当前产品详情
    loadCurrentProduct(productId);
    
    // 重置比较产品选择
    document.getElementById('compareProductSelect').value = '';
    document.getElementById('compareProductContainer').style.display = 'none';
    
    // 显示模态框
    const compareModal = new bootstrap.Modal('#compareModal');
    compareModal.show();
}

// 加载当前产品详情
function loadCurrentProduct(productId) {
    // 这里直接从页面中获取已加载的产品信息
    const productItem = document.querySelector(`[data-product-id="${productId}"]`).closest('.cart-item');
    
    if (productItem) {
        document.getElementById('currentProductName').textContent = 
            productItem.querySelector('.fw-medium').textContent;
        document.getElementById('currentProductImage').src = 
            productItem.querySelector('.product-image').src;
        document.getElementById('currentProductPrice').textContent = 
            productItem.querySelector('.text-center.fw-medium .fw-medium').textContent;
        
        // 从PHP生成的数据中获取完整产品信息
        const allProducts = <?php 
            $allProducts = [];
            $allProductsQuery = "SELECT * FROM product";
            $result = $conn->query($allProductsQuery);
            while ($row = $result->fetch_assoc()) {
                $allProducts[$row['product_id']] = $row;
            }
            echo json_encode($allProducts);
        ?>;
        
        const currentProduct = allProducts[productId];
        if (currentProduct) {
            // 更新销售数量
            document.getElementById('currentProductSoldQuantity').textContent = 
                currentProduct.sold_quantity || '0';
                document.getElementById('currentProductDescription').textContent = 
                currentProduct.description || '-';
        }
    }
}

// 产品选择变化事件
document.getElementById('compareProductSelect').addEventListener('change', function() {
    const selectedProductId = this.value;
    
    if (selectedProductId) {
        // 从PHP生成的数据中获取产品信息
        const allProducts = <?php echo json_encode($allProducts ?? []); ?>;
        const product = allProducts[selectedProductId];
        
        if (product) {
            document.getElementById('compareProductContainer').style.display = 'block';
            document.getElementById('compareProductName').textContent = product.product_name;
            document.getElementById('compareProductImage').src = '../../' + product.product_image;
            document.getElementById('compareProductPrice').textContent = 'RM' + parseFloat(product.unit_price).toFixed(2);
            document.getElementById('compareProductDescription').textContent = product.description || 'No description available';
            
            // 更新销售数量
            document.getElementById('compareProductSoldQuantity').textContent = 
                product.sold_quantity || '0';
        }
    } else {
        document.getElementById('compareProductContainer').style.display = 'none';
    }
});

document.querySelector('.checkout-btn').addEventListener('click', function (e) {
    e.preventDefault();  // 防止默认提交
    let selected = [];
    
    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        selected.push(checkbox.getAttribute('data-product-id'));
    });

    if (selected.length === 0) {
        // 显示美观的提示模态框
        const alertModal = new bootstrap.Modal('#alertModal');
        document.getElementById('alertMessage').textContent = "Please select at least one product.";
        alertModal.show();
        return;
    }

    document.getElementById('selectedProducts').value = JSON.stringify(selected);
    document.getElementById('checkoutForm').submit();
});
});
</script>