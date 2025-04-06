<?php
    session_start();
    include '..\..\includes\database.php';
    include '..\..\includes\product_page_functions.php';

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
    $user_id = $_SESSION['user_id'] ?? null;
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../authentication/login.php");
        exit();
    }

    $selected_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

    $product = getCompleteProductData($conn, $product_id);

    if (!$product) {
        die("Product not found");
    }

    $avg_rating = $product['avg_rating'];
    $rounded_rating = $product['rounded_rating']; 
    $total_reviews = $product['total_reviews'];

    //display star
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_POST['quantity'])) {
        $product_id = $_SESSION['selected_product_id'];
        $quantity = $_POST['quantity'];
        $user_id = $_SESSION['user_id'];

        $result = addToCart($conn, $product_id, $quantity, $user_id);
        
        if ($result['success']) {
            header("Location: product_page.php?added=1");
        } else {
            header("Location: product_page.php?error=" . $result['error'] . "&available=" . $result['available']);
        }
        exit();
    }

    $vendor = VendorDetail($conn, $product_id);
    if ($vendor) {
        $store_name = $vendor['store_name'];
        $vendor_id = $vendor['vendor_id'];
        
        $vendor_rating = VendorRating($conn, $vendor_id);
    } else {
        $store_name = "Unknown Store";
        $vendor_rating = 0;
    }

    
    //get each rating
    $rating_counts = [];
    for ($i = 1; $i <= 5; $i++) {
    $query = "SELECT COUNT(*) FROM review WHERE product_id = ? AND rating = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $i);
    $stmt->execute();
    $rating_counts[$i] = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    }

    //get comment with filtering and sorting
    $query = "SELECT r.rating, r.review_description, r.review_date, u.first_name, u.last_name 
          FROM review r 
          JOIN `user` u ON r.user_id = u.user_id 
          WHERE r.product_id = ?";
    
    // Add rating filter if selected
    if ($selected_rating > 0) {
        $query .= " AND r.rating = ?";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($selected_rating > 0) {
        $stmt->bind_param("ii", $product_id, $selected_rating);
    } else {
        $stmt->bind_param("i", $product_id);
    }
    
    $stmt->execute();
    $reviews_result = $stmt->get_result();
    $reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Page</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/product_page.css?v=<?= filemtime('../../css/product_page.css') ?>">
</head>

<body class="product_page">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
    <div class="product-box">
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
                <h3 class="text-danger custom-price">RM<?php echo number_format($product['unit_price'], 2); ?></h3>


                <!-- stock quantity -->
                <p class="mt-3"><span style="color: #888888;">Stock:</span> <?php echo $product['stock_quantity']; ?> Available</p>                <!-- description -->
                <div class="mt-4">
                    <h5>Product Description :</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <div class="d-flex align-items-center space">
                        <h5 class="mb-0 me-2">Product Weight:</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($product['weight']); ?></p>
                    </div>
                </div>

                <!-- dropdown shopping protection -->
                <div class="mt-4 shopping-protection-container">
                    <div class="shopping-protection-trigger d-inline-block">
                    <i class="bi bi-shield-fill-check"></i>&nbsp;
                        <span>Shopping Protection</span>
                        <small class="text-muted ms-2">15-Day Free Return · Cash On Delivery (COD)</small>
                    </div>
                    <div class="shopping-protection-content">
                        <div class="card card-body bg-light mt-2 p-3">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2"> 
                                <i class="bi bi-box-arrow-in-left me-2"></i> 
                                <h6 class="fw-bold mb-0">15-Day Free Return</h6>  
                            </div>
                            <p class="mb-0 small">
                                Enjoy unconditional full refund within 15 days. Applies to eligible items. Electronic products must remain sealed (original packaging unbroken/unmodified). See terms for eligible products.
                            </p>
                        </div>
                            <div class="mb-3">
                                <h6 class="fw-bold"><img src="../../Assets/img/cash-on-delivery-cod-icon-260nw-2302469373 (1)-Photoroom.png" alt="COD Icon" style="height: 20px;"> Cash On Delivery (COD)</h6>
                                <div class="small">
                                    <ol class="mb-0 ps-3">
                                        <li class="small">In Malaysia, Shopee offers COD for door-to-door deliveries or orders below RM250.</li>
                                        <li class="small">Your order will be delivered by Shopee Express, DHL eCommerce or Ninjavan. </li>
                                        <li class="small">Pay cash upon delivery.</li>
                                        <li class="small">Note: Payment amount will be rounded to the nearest 5 sen.</li>
                                        <li class="small">Important: Beware of fraud - never pay sellers directly for COD orders.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
 

                <!-- button -->
            <div class="mt-4 d-flex gap-3">
                <button type="button" class="btn btn-outline-danger px-4 py-2" style="width: 180px;" 
                    data-bs-toggle="modal" data-bs-target="#quantityModal"
                    data-stock-quantity="<?php echo $product['stock_quantity']; ?>">
                    <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                </button>
                <button class="btn btn-danger px-4 py-2" style="width: 180px;" 
                    data-bs-toggle="modal" data-bs-target="#buyNowModal"
                    data-stock-quantity="<?php echo $product['stock_quantity']; ?>">
                    Buy Now
                </button>
            </div>
        </div>
        </div>
    </div>

    <!-- Vendor -->
    <div class="product-box p-4 mt-4">
        <div class="row align-items-center">
            <!-- Vendor Image -->
            <div class="col-md-2 text-center">
            <a href="../vendor/vendor_product_page.php?vendor_id=<?php echo htmlspecialchars($vendor_id); ?>" class="btn p-0 border-0 bg-transparent"><!--  Change Vendor Profile -->
                        <img src="../../Assets/img/product_img/vendor_image.jpeg" 
                            alt="Vendor Image" 
                            class="img-fluid rounded-circle" 
                            style="width: 150px; height: 150px; object-fit: cover;">
                     </a>
            </div>
            <!-- Vendor Info -->
            <div class="col-md-10">
                <h4 class="mb-2"><?php echo htmlspecialchars($store_name); ?></h4>
                <p class="mb-1">
                    Vendor Rating: 
                    <?php 
                    if ($vendor_rating > 0) {
                        echo htmlspecialchars($vendor_rating) . ' / 5 ';
                        echo '<span class="text-muted">' . displayStars($vendor_rating) . '</span>';
                    } else {
                        echo 'No Rating Yet';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    
    <!-- comment section -->
    <section id="comment">
        <div class="comment-box">
            <div class="border-bottom pb-3 mb-4">
                <h4>Product Review</h4>
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <h2 class="text-warning mb-0"><?php echo number_format($avg_rating, 1); ?><small class="text-muted fs-6">/5</small></h2>
                        <div class="text-warning">
                            <?php echo displayStars($rounded_rating); ?>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <div class="rating-filter">
                                <a href="?rating=0" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 0 ? 'active' : '' ?>">All</a>
                                <a href="?rating=5" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 5 ? 'active' : '' ?>">5 Stars (<?= $rating_counts[5] ?? 0 ?>)</a>
                                <a href="?rating=4" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 4 ? 'active' : '' ?>">4 Stars (<?= $rating_counts[4] ?? 0 ?>)</a>
                                <a href="?rating=3" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 3 ? 'active' : '' ?>">3 Stars (<?= $rating_counts[3] ?? 0 ?>)</a>
                                <a href="?rating=2" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 2 ? 'active' : '' ?>">2 Stars (<?= $rating_counts[2] ?? 0 ?>)</a>
                                <a href="?rating=1" class="btn btn-sm btn-outline-secondary <?= $selected_rating == 1 ? 'active' : '' ?>">1 Star (<?= $rating_counts[1] ?? 0 ?>)</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Each Comment -->
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $row): ?>
                    <div class="border-bottom pb-4 mb-4 d-flex">
                        <div class="me-3">
                            <i class="bi bi-person-circle" style="font-size: 30px;"></i>
                        </div>    
                        <!-- content -->
                        <div class="flex-grow-1">
                            <!-- username -->
                            <div class="fw-bold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                            <!-- rating -->
                            <div class="text-warning">
                                <?= displayStars($row['rating']); ?>
                            </div>
                            <!-- date -->
                            <div class="text-muted small mb-2"><?= $row['review_date']; ?></div>
                            <!-- description -->
                            <div class="mb-2">
                                <p class="mb-1"><?= htmlspecialchars($row['review_description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <p>No reviews found for this filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- related item -->
    <section id="related_item">
        <div class="related-box">
            <h4 class="mb-4">Related Item</h4>
            <div class="row">
                <?php
                //current product id
                $current_category_id = $product['category_id'];
                
                // show other product
                $related_query = "SELECT * FROM product 
                                WHERE category_id = ? 
                                AND product_id != ? 
                                AND product_status = 'Approved'
                                ORDER BY product_name ASC"; // order by name
                
                $related_stmt = $conn->prepare($related_query);
                $related_stmt->bind_param("ii", $current_category_id, $product_id);
                $related_stmt->execute();
                $related_result = $related_stmt->get_result();
                
                if ($related_result->num_rows > 0) {
                    while ($related_product = $related_result->fetch_assoc()) {
                        echo '
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                            <div class="card h-100 related-product-card">
                                <img src="../../'.htmlspecialchars($related_product['product_image']).'" 
                                    class="card-img-top p-3" 
                                    alt="'.htmlspecialchars($related_product['product_name']).'"
                                    style="height: 180px; object-fit: contain;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title" style="font-size: 1rem;">'.htmlspecialchars($related_product['product_name']).'</h5>
                                    <div class="mt-auto">
                                        <p class="card-text text-danger fw-bold mb-2">RM'.number_format($related_product['unit_price'], 2).'</p>
                                        <a href="product_page.php" 
                                        class="btn btn-outline-danger w-100" 
                                        onclick="event.preventDefault(); 
                                                    document.getElementById(\'related_product_id\').value = '.$related_product['product_id'].';
                                                    document.getElementById(\'related_product_form\').submit();">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12"><p class="text-muted">No other products found in this category.</p></div>';
                }
                ?>
            </div>
            
            <!-- Hidden form for product jump -->
            <form id="related_product_form" action="product_page.php" method="post" style="display: none;">
                <input type="hidden" name="product_id" id="related_product_id">
            </form>
        </div>
    </section>
    
    <!-- add to cart form -->
    <div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <form method="post" action="" id="cartForm">
            <input type="hidden" name="add_to_cart" value="1">
            <div class="modal-header">
            <h5 class="modal-title" id="quantityModalLabel">Please enter the purchase quantity          </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="quantityInput" class="form-label">Quantity (Available: <span id="availableStock"><?php echo $product['stock_quantity']; ?></span>)</label>
                    <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" value="1" required>
                    <div class="invalid-feedback">The quantity must be at least 1</div>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-outline-danger">Add to Cart</button>
            </div>
        </form>
        </div>
    </div>
    </div>

    <!-- Buy now form -->
    <div class="modal fade" id="buyNowModal" tabindex="-1" aria-labelledby="buyNowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <form method="post" action="check_out.php" id="buyNowForm">
            <input type="hidden" name="product_id" value="<?php echo $_SESSION['selected_product_id']; ?>">
            <div class="modal-header">
            <h5 class="modal-title" id="buyNowModalLabel">Please enter the purchase quantity</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="modal-body">
                <div class="mb-3">
                    <label for="buyNowQuantity" class="form-label">Quantity (Available: <span id="availableStock"><?php echo $product['stock_quantity']; ?></span>)</label>
                    <input type="number" class="form-control" id="buyNowQuantity" name="quantity" min="1" value="1" required>
                    <div class="invalid-feedback">The quantity must be at least 1</div>
                </div>
            </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-outline-danger">Proceed to Checkout</button>
            </div>
        </form>
        </div>
    </div>
    </div>
</div>

    <?php include '../../includes/footer.php';?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/main_page.js"></script>
    <script src="../../js/product_page.js"></script>
</body>

</html>

