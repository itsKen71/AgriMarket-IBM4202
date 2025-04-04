<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}
include '../../includes/database.php';

$categories = getCategories();

$selected_category_id = $_GET['category_id'] ?? 'all';
$search_query = $_GET['search'] ?? ''; 
$filter = $_GET['filter'] ?? ''; 

$products = getApprovedProducts($selected_category_id, $search_query, $filter); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Main Page</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main_page.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="main_page">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <!-- Search Bar and Filter Button -->
        <div class="search-filter-container d-flex justify-content-between align-items-center mb-4">
            <form action="main_page.php" method="GET" class="d-flex flex-grow-1 me-3">
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
        <div class="collapse" id="filterCollapse">
            <div class="card card-body">
                <h5 class="mb-3">Filter Options</h5>
                <div class="row">
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=price_asc">Price: Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=price_desc">Price: High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=stock_asc">Stock: Low to High</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=stock_desc">Stock: High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=sold_asc">Sold Quantity: Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=sold_desc">Sold Quantity: High to Low</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=weight_asc">Weight: Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=weight_desc">Weight: High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none" href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=recent">Recently Added</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a class="btn btn-danger text-white" href="main_page.php?category_id=<?php echo htmlspecialchars($selected_category_id); ?>">Clear Filter</a>
                </div>
            </div>
        </div>

        <!-- Category Navigation -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <button class="btn btn-outline-success" id="prevCategory">&lt;</button>
            <div class="d-flex category-container">
                <?php foreach (['all' => 'All Products'] + array_column($categories, 'category_name', 'category_id') as $category_id => $category_name): ?>
                    <button
                        class="btn btn-outline-success category-btn <?php echo ($selected_category_id == $category_id || ($category_id === 'all' && $selected_category_id === null)) ? 'active' : ''; ?>"
                        data-category="<?php echo $category_id === 'all' ? 'all' : htmlspecialchars($category_id); ?>">
                        <?php echo htmlspecialchars($category_name); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-outline-success" id="nextCategory">&gt;</button>
        </div>

        <h2 class="mb-3 product-title">Discovery</h2>

        <!-- Products Section -->
        <div class="row" id="productContainer">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <form action="product_page.php" method="POST" class="h-100">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <div class="card h-100 clickable-card" onclick="this.closest('form').submit();">
                            <img src="<?php echo '../../' . htmlspecialchars($product['product_image']); ?>"
                                class="card-img-top product-image"
                                alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                style="height: 250px; object-fit: cover;">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/main_page.js"></script>
</body>

</html>