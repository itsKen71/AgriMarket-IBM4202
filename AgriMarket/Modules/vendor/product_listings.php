<?php
session_start();

include '../../includes/database.php';

$vendor_id = 1;//temporary use for testing 
//$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$vendor_id) {
    header("Location: ../../authentication/login.php"); // Redirect to login page
    exit(); // 
}


$approvedProducts = getProductsByStatus($conn, $vendor_id, 'Approved');
$pendingProducts = getProductsByStatus($conn, $vendor_id, 'Pending');
$rejectedProducts = getProductsByStatus($conn, $vendor_id, 'Rejected');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Listings</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/product_listings.css">
    <script src="../../js/product_listings.js"></script>
</head>

<body class="product_listings">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <!-- Content Start Here -->
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
                                            <th>Description</th>
                                            <th>Stock</th>
                                            <th>Weight (kg)</th>
                                            <th>Packaging</th>
                                            <th>Price</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $approvedProducts->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?></td>
                                                <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($product['packaging_type']); ?></td>
                                                <td>$<?php echo number_format($product['unit_price'], 2); ?></td>
                                                <td class="text-center align-middle">
                                                    <button class="btn btn-primary btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                        data-id="<?php echo $product['product_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                                        data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                        data-stock="<?php echo $product['stock_quantity']; ?>"
                                                        data-weight="<?php echo $product['weight']; ?>"
                                                        data-packaging="<?php echo $product['packaging_type']; ?>"
                                                        data-price="<?php echo $product['unit_price']; ?>">
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
                                            <th>Description</th>
                                            <th>Stock</th>
                                            <th>Weight (kg)</th>
                                            <th>Packaging</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $pendingProducts->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?></td>
                                                <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($product['packaging_type']); ?></td>
                                                <td>$<?php echo number_format($product['unit_price'], 2); ?></td>
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
                                            <th>Description</th>
                                            <th>Stock</th>
                                            <th>Weight (kg)</th>
                                            <th>Packaging</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $rejectedProducts->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?></td>
                                                <td><?php echo $product['weight'] ? $product['weight'] : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($product['packaging_type']); ?></td>
                                                <td>$<?php echo number_format($product['unit_price'], 2); ?></td>
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
                <form action="../../includes/edit_products.php" method="POST">
                    <input type="hidden" name="product_id" id="editProductId">

                    <div class="mb-3">
                        <label for="editProductName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editProductName" name="product_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editStock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="editStock" name="stock_quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="editWeight" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="editWeight" name="weight" required>
                    </div>

                    <div class="mb-3">
                        <label for="editPackaging" class="form-label">Packaging Type</label>
                        <select class="form-select" id="editPackaging" name="packaging_type" required>
                            <option value="Normal">Normal</option>
                            <option value="More Protection">More Protection</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="editPrice" name="unit_price" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Add Button -->
    <img src="../../Assets/img/add-circle.png" alt="Add Product" class="add-btn" id="addProductBtn">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
