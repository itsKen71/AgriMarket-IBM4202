<?php

if (!isset($_SESSION['role'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Hau Tien';
$role = $_SESSION['role'];
$homeLink = "#";

if ($role === "Customer") {
    $homeLink = "../customer/main_page.php";
} elseif ($role === "Vendor") {
    $homeLink = "../customer/main_page.php";
} elseif ($role === "Staff") {
    $homeLink = "../staff/staff_dashboard.php";
} elseif ($role === "Admin") {
    $homeLink = "../admin/admin_dashboard.php";
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/header.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $homeLink; ?>">
                <img src="../../Assets/svg/temp_logo.svg" alt="logo" class="logo">
                AgriMarket
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Customer & Vendor Links -->

                    <?php if ($role === "Customer" || $role === "Vendor"): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'cart.php') ? 'active' : ''; ?>"
                                href="../customer/cart.php">Cart</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'order_history.php') ? 'active' : ''; ?>"
                                href="../customer/order_history.php">Order History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'subscription_listing.php') ? 'active' : ''; ?>"
                                href="../vendor/subscription_listing.php">Subscription Listing</a>
                        </li>
                    <?php endif; ?>

                    <!-- Vendor-Only Links -->
                    <?php if ($role === "Vendor"): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'product_listings.php') ? 'active' : ''; ?>"
                                href="../vendor/product_listings.php">Product Listings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'analytics_dashboard.php') ? 'active' : ''; ?>"
                                href="../admin/analytics_dashboard.php">Analytics Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <!-- Staff Links -->
                    <?php if ($role === "Staff"): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'staff_dashboard.php') ? 'active' : ''; ?>"
                                href="../staff/staff_dashboard.php">Staff Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'main_page.php') ? 'active' : ''; ?>"
                                href="../customer/main_page.php">Main Page</a>
                        </li>
                    <?php endif; ?>

                    <!-- Admin Links -->
                    <?php if ($role === "Admin"): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'admin_dashboard.php') ? 'active' : ''; ?>"
                                href="../admin/admin_dashboard.php">Admin Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'analytics_dashboard.php') ? 'active' : ''; ?>"
                                href="../admin/analytics_dashboard.php">Analytics Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <!-- Profile Icon -->
                    <li class="nav-item dropdown profile-item">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../../Assets/svg/person-circle.svg" alt="Profile">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="navbarDropdown">
                            <li class="dropdown-item-text fw-bold text-dark text-center">
                                <?php echo htmlspecialchars($username); ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php if ($role === "Customer"): ?>
                                <li><a class="dropdown-item" href="../customer/customer_profile.php">Profile</a></li>
                            <?php elseif ($role === "Vendor"): ?>
                                <li><a class="dropdown-item" href="../vendor/vendor_profile.php">Profile</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item d-flex justify-content-center align-items-center"
                                    href="../authentication/login.php">
                                    <img src="../../Assets/svg/box-arrow-left.svg" alt="Logout" width="20" height="20"
                                        class="me-2">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</body>


</html>