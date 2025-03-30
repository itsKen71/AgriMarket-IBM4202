<?php
session_start();
include '../../includes/database.php';

$categories = getCategories();

$selected_category_id = $_GET['category_id'] ?? 'all';

$products = getApprovedProducts($selected_category_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Main Page</title>
    <link rel="icon" type="image/png" href="../../assets/img/temp-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main_page.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="main_page">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
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