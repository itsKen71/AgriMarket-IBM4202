<?php
    session_start();
    include '..\..\includes\database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
        $_SESSION['selected_product_id'] = $_POST['product_id'];
        header("Location: product_page.php");
        exit();
    }

    if (!isset($_SESSION['selected_product_id'])) {
        header("Location: main_page.php");
        exit();
    }

    $product_id = $_SESSION['selected_product_id'];
    $user_id = 1;

    $query = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $query = "SELECT COUNT(*) as total_reviews, AVG(rating) as avg_rating FROM review WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $review_stats = $result->fetch_assoc();
    $stmt->close();

    $avg_rating = $review_stats['avg_rating'] ?? 0;
    $rounded_rating = round($avg_rating);
    $total_reviews = $review_stats['total_reviews'] ?? 0;
    function displayStars($rating) {
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Page</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/product_page.css">
    <style>
        .product-image {
            width: 100%;
            max-width: 450px;
            max-height: 450px;
            object-fit: contain;
            cursor: pointer;
        }

        .text-muted i {
            color: gold; /* Make stars gold/yellow */
            margin-right: 2px; 
        }

        /* 悬停效果样式 */
    .shopping-protection-container {
        position: relative;
        display: inline-block;
    }
    
    .shopping-protection-content {
        display: none;
        position: absolute;
        z-index: 1000;
        min-width: 300px;
        left: 0;
    }
    
    .shopping-protection-trigger:hover + .shopping-protection-content,
    .shopping-protection-content:hover {
        display: block;
    }
    </style>

</head>

<body class="product_page">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->
        <div class="row">
        <div class="col-md-5">
                <img src="../../<?php echo htmlspecialchars($product['product_image']); ?>" class="product-image mb-3">
        </div>

        <div class="col-md-7">
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p class="text-muted">
                    <?php echo displayStars($rounded_rating); ?>
                    <?php echo number_format($avg_rating, 1); ?> &nbsp;|&nbsp; 
                    <?php echo number_format($total_reviews);?> Ratings &nbsp;|&nbsp; 
                    <?php echo htmlspecialchars($product['sold_quantity'] ?? '0'); ?> Sold
                </p>
                <h3 class="text-danger">RM<?php echo number_format($product['unit_price'], 2); ?></h3>


                <!-- stock quantity -->
                <p class="mt-3"><strong>Stock: </strong><?php echo $product['stock_quantity']; ?> Available</p>

                <!-- description -->
                <div class="mt-4">
                    <h5>Product Description :</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 me-2">Product Weight:</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($product['weight']); ?></p>
                    </div>
                </div>
                
                <!-- dropdown shopping protection -->
                <div class="mt-4 shopping-protection-container">
                <div class="shopping-protection-trigger d-inline-block">
                    <i class="fas fa-shield-alt me-2 text-primary"></i>
                    <span>购物保障</span>
                    <small class="text-muted ms-2">15 天免费退货 · 货到付款 · Program Servis Penjagaan Produk</small>
                </div>
                

                <div class="shopping-protection-content">
                    <div class="card card-body bg-light mt-2">
                        <div class="mb-3">
                            <h6 class="fw-bold">15 天免费退货</h6>
                            <p class="mb-0 small">
                                在15天内申请免费退货，可享受无条件的全额退款。适用于特定商品。电子产品类别必须保持密封（即封条未损坏、未被篡改或修改)。特定商品詳情。需符合条款和条件。
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">货到付款</h6>
                            <p class="mb-0 small">
                                <ol>
                                    <li>在马来西亚， Shopee为送货上门或250令吉以下的订单提供货到付款服务</li>
                                    <li>您的订单将交由Shopee Express、 DHL eCommerce 或 Ninjavan 配送 </li>
                                    <li>包裹送达时向快递员支付现金。</li>
                                    <li>请注意， 应支付的金额将四舍五入至最接近的5仙。</li>
                                    <li>提醒您提防欺诈行为，货到付款订单无需直接向卖家支付任何款项。</li>
                                </ol>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
 

                <!-- button -->
                <div class="mt-4">
                    <button class="btn btn-warning btn-lg">Buy Now</button>
                    <button class="btn btn-danger btn-lg">Add to Cart</button>
                </div>
            </div>
        </div>

        <!-- Testing  -->
        <h1>Selected Product ID: <?php echo htmlspecialchars($product_id); ?></h1>


    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>