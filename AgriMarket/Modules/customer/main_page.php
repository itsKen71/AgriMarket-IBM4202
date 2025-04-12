<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}
include '../../includes/database.php';

$db = new Database();
$productClass = new Product($db);

$categories = $productClass->getCategories();

$selected_category_id = $_GET['category_id'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

// Log the search query if it's not empty
if (!empty($search_query) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $timestamp = time();
    $log_data = [
        'user_id' => $user_id,
        'term' => $search_query,
        'timestamp' => $timestamp,
    ];
    $log_entry = json_encode($log_data);
    $log_file = '../../data/search_logs.txt';
    // Ensure directory exists
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
}

$user_id = $_SESSION['user_id'];
$recent_searches = [];
$log_file = '../../data/search_logs.txt';

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data && $data['user_id'] == $user_id) {
            $recent_searches[] = [
                'term' => mb_strtolower(trim($data['term'])), 
                'timestamp' => $data['timestamp']
            ];
        }
    }
}

usort($recent_searches, function ($a, $b) {
    return $b['timestamp'] <=> $a['timestamp'];
});

$unique_terms = [];
foreach ($recent_searches as $search) {
    if (!in_array($search['term'], $unique_terms)) {
        $unique_terms[] = $search['term'];
    }
    if (count($unique_terms) >= 10) {
        break;
    }
}

$top_searches = $unique_terms; 

$products = $productClass->getApprovedProducts(
    $selected_category_id,
    $search_query,
    $filter,
    $top_searches 
); 
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
                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($selected_category_id); ?>">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
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

        <!-- Display Frequent Search Terms -->
        <?php if (!empty($top_searches)): ?>
            <div class="mb-3">
                <h5>Your Frequent Searches:</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($top_searches as $term): ?>
                        <a href="main_page.php?search=<?php echo urlencode($term); ?>" class="btn btn-sm btn-outline-success">
                            <?php echo htmlspecialchars($term); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="collapse" id="filterCollapse">
            <div class="card card-body">
                <h5 class="mb-3">Filter Options</h5>
                <div class="row">
                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=price_asc">Price:
                                    Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=price_desc">Price:
                                    High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=stock_asc">Stock:
                                    Low to High</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=stock_desc">Stock:
                                    High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=sold_asc">Sold
                                    Quantity: Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=sold_desc">Sold
                                    Quantity: High to Low</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <ul class="list-unstyled">
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=weight_asc">Weight:
                                    Low to High</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=weight_desc">Weight:
                                    High to Low</a>
                            </li>
                            <li>
                                <a class="btn btn-link text-decoration-none"
                                    href="?category_id=<?php echo htmlspecialchars($selected_category_id); ?>&filter=recent">Recently
                                    Added</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a class="btn btn-danger text-white"
                        href="main_page.php?category_id=<?php echo htmlspecialchars($selected_category_id); ?>">Clear
                        Filter</a>
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

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/main_page.js"></script>
</body>

</html>