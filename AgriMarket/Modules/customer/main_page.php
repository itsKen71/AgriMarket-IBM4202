<?php
session_start();
include '..\..\includes\database.php';
$products = getApprovedProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Main Page</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main_page.css">
</head>

<body class="main_page">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-3">Products</h2>
        <div class="row">
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
</body>

</html>