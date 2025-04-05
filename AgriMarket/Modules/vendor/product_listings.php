<?php
session_start();

include '../../includes/database.php';

$user_id = $_SESSION['user_id'] ?? null;
$vendor = getVendorDetails($user_id, $conn);
if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php"); // Redirect to login page
    exit(); // 
}
if (!$vendor) {
    echo "Error: Vendor profile not found.";
    exit();
}
$vendor_id = $vendor['vendor_id'];

if ($vendor['has_low_stock_alert']) {
    $lowStockProducts = getLowStockProducts($vendor['vendor_id'], $conn);
    $lowStockProductsJson = json_encode($lowStockProducts);
}
$approvedProducts = getProductsByStatus($conn, $vendor_id, 'Approved');
$pendingProducts = getProductsByStatus($conn, $vendor_id, 'Pending');
$rejectedProducts = getProductsByStatus($conn, $vendor_id, 'Rejected');

$uploadLimit = $vendor['upload_limit'];
$pendingCount = getPendingProductCount($vendor_id, $conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Listings</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/product_listings.css">
    <script src="../../js/product_listings.js"></script>
</head>

<body class="product_listings" 
    data-success="<?php echo isset($_GET['add']) && $_GET['add'] == 'success' ? 'true' : 'false'; ?>" 
    data-edit-success="<?php echo isset($_GET['edit']) && $_GET['edit'] == 'success' ? 'true' : 'false'; ?>">
    
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Product Listings</h2>
        <div class="accordion" id="productAccordion">

            <!-- Products Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingProducts">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="true">
                        Products
                    </button>
                </h2>
                <div id="collapseProducts" class="accordion-collapse collapse show" aria-labelledby="headingProducts">
                    <div class="accordion-body">
                        <!-- product listing  -->
                        <?php if ($approvedProducts->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Stock</th>
                                        <th>Weight (kg)</th>
                                        <th>Price</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $approvedProducts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                            <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                            <td>RM<?php echo number_format($product['unit_price'], 2); ?></td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-success btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                data-id="<?php echo $product['product_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                                data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                data-stock="<?php echo $product['stock_quantity']; ?>"
                                                data-weight="<?php echo $product['weight']; ?>"
                                                data-price="<?php echo $product['unit_price']; ?>"
                                                data-image="<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : ''; ?>"
                                                > 
                                                Edit
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <p class="text-muted">No items found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

            <!-- Pending Requests Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPending">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePending">
                        Pending Requests
                    </button>
                </h2>
                <div id="collapsePending" class="accordion-collapse collapse" aria-labelledby="headingPending">
                    <div class="accordion-body">
                        <!-- pending request list  -->
                        <?php if ($pendingProducts->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Stock</th>
                                            <th>Weight (kg)</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $pendingProducts->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?></td>
                                                <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                                <td>RM<?php echo number_format($product['unit_price'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No items found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rejected Products Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingRejected">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRejected">
                        Rejected
                    </button>
                </h2>
                <div id="collapseRejected" class="accordion-collapse collapse" aria-labelledby="headingRejected">
                    <div class="accordion-body">
                        <!-- rejected product list  -->
                        <?php if ($rejectedProducts->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Category</th> 
                                            <th>Description</th>
                                            <th>Stock</th>
                                            <th>Weight (kg)</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $rejectedProducts->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?></td>
                                                <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                                <td>RM<?php echo number_format($product['unit_price'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No items found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../includes/edit_products.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="editProductId">

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editProductName" readonly> <!-- Read-only input -->
                    </div>

                    <!-- Product Image Upload -->
                    <div class="mb-3">
                        <label for="editProductImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="product_image" id="editProductImage" accept="image/*">
                        <!-- Image path -->
                        <input type="hidden" name="current_image" id="currentImage">
                        <!-- Image name -->
                        <input type="hidden" name="current_image_name" id="currentImageName">
                        <!-- Image Preview -->
                        <div class="mt-2 mb-3">
                            <img id="imagePreview" src="" alt="Current Image" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editStock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" name="stock_quantity" id="editStock" required>
                    </div>

                    <div class="mb-3">
                        <label for="editWeight" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" class="form-control" name="weight" id="editWeight">
                    </div>

                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" name="unit_price" id="editPrice" required>
                    </div>

                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <select class="form-control" name="category_id" id="editCategory" required>
                            <?php
                            $categories = getCategories(); 
                            foreach ($categories as $category) {  
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../includes/add_products.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="vendor_id" value="<?php echo $vendor_id; ?>">

                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="product_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="product_image" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock" name="stock_quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="weight" name="weight" required>
                    </div>

                    <div class="mb-3">
                        <label for="unitPrice" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="unitPrice" name="unit_price" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category_id" id="category" required>
                            <option value="" disabled selected>Select Category</option>
                            <?php
                            $categories = getCategories(); 
                            foreach ($categories as $category) { 
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Product added successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Success Modal -->
<div class="modal fade" id="editSuccessModal" tabindex="-1" aria-labelledby="editSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Product updated successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;" id="toastContainer">
    <!-- JS add toast per product -->
</div>


<!-- Add Button -->
<img src="../../assets/img/add-circle.png" alt="Add Product" class="add-btn" id="addProductBtn"
    data-bs-toggle="modal" data-bs-target="#addProductModal"
    <?php if ($pendingCount >= $uploadLimit) echo 'style="pointer-events: none; opacity: 0.5;"'; ?> >

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>window.lowStockProducts = <?php echo $lowStockProductsJson; ?>;</script> <!-- Pass php data to js -->
</body>

</html>

