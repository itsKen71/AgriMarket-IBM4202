<?php
session_start();
include '../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

// Temp
$vendor_id = 3;

$vendor = getVendorDetailsById($conn, $vendor_id);

$search_query = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$products = getVendorProducts($conn, $vendor_id, $search_query, $filter);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Vendor Product Page</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/vendor_product_page.css">
</head>

<body class="vendor_product_page">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <!-- Vendor Profile Card -->
        <div class="vendor-profile-card mb-4 shadow-sm">
            <div class="card-body">
                <h3 class="card-title"><?php echo htmlspecialchars($vendor['store_name']); ?></h3>
                <p class="card-text">
                    <strong>Owner:</strong>
                    <?php echo htmlspecialchars($vendor['first_name'] . ' ' . $vendor['last_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($vendor['email']); ?><br>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($vendor['phone_number']); ?>
                </p>
            </div>
        </div>

        <!-- Search Bar and Filter Button -->
        <div class="search-filter-container d-flex justify-content-between align-items-center mb-4">
            <form action="vendor_product_page.php" method="GET" class="d-flex flex-grow-1 me-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search for products..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-success" type="submit">Search</button>
                </div>
            </form>

            <button class="btn btn-outline-success d-flex align-items-center" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                Filter&nbsp;&nbsp;&nbsp;&#9660;
            </button>
        </div>

        <!-- Filter Section -->
        <div class="collapse" id="filterCollapse">
            <div class="card card-body">
                <h5 class="mb-3">Filter Options</h5>
                <div class="row">
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=price_asc">Price: Low to
                                    High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=price_desc">Price: High to
                                    Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=stock_asc">Stock: Low to
                                    High</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=stock_desc">Stock: High to
                                    Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=sold_asc">Sold Quantity: Low
                                    to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=sold_desc">Sold Quantity:
                                    High to Low</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=weight_asc">Weight: Low to
                                    High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=weight_desc">Weight: High to
                                    Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?filter=recent">Recently Added</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a class="btn btn-danger text-white" href="vendor_product_page.php">Clear Filter</a>
                </div>
            </div>
        </div>

        <!-- Product Section -->
        <div class="row" id="productContainer">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <form action="../customer/product_page.php" method="POST" class="h-100">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <div class="card h-100 clickable-card" onclick="this.closest('form').submit();">
                            <img src="<?php echo '../../' . htmlspecialchars($product['product_image']); ?>"
                                class="card-img-top product-image"
                                alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-muted">RM<?php echo number_format($product['unit_price'], 2); ?></h5>
                                    <span class="badge bg-success">In Stock:
                                        <?php echo $product['stock_quantity']; ?></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>