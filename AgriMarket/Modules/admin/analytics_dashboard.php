<?php
session_start();
// Retrieve user ID and role from the session, or set them to null if not available
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Redirect to the login page if the user is not logged in or role is not set
if (!$user_id && !$role) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

// Include the database connection file
include '../../includes/database.php';

// Initialize the database connection and required class objects
$db = new Database();
$userClass = new User($db);
$customerClass = new Customer($db);
$vendorClass = new Vendor($db);
$adminClass = new Admin($db);
$productClass = new Product($db);
$paymentClass = new Payment($db);

// Check if the user role is Admin; otherwise, set the vendor ID for queries
$checkRole = ($role == "Admin");
$query_vendor_id = -1;

if (!$checkRole) {
    // Get the vendor ID associated with the logged-in user
    $query_vendor_id = $userClass->getVendorIdByUserId($user_id);
}

// Retrieve the selected time filter option (default is 'monthly')
$option = $_GET['option'] ?? 'monthly';

// Fetch analytics data based on the user's role and vendor ID
$activeUsers = $userClass->getActiveUser(); // Get the count of active users
$numberProduct = $productClass->getNumberProducts($query_vendor_id); // Get the number of products
$refundPercentage = $paymentClass->getRefundPercentage($query_vendor_id); // Get refund percentage
$subscriptionData = $vendorClass->getSubscription(); // Get subscription data
$topPaymentMethod = $paymentClass->topPaymentmethod($query_vendor_id); // Get the top payment method
$topVendor = $adminClass->getTopVendor(); // Get the top-performing vendors
$topProduct = $productClass->getTopProduct($query_vendor_id); // Get the top-selling products
$shipmentStatus = $customerClass->getShipmentStatus($query_vendor_id); // Get shipment status
$monthlyOrders = $paymentClass->getOrders($query_vendor_id, $option); // Get monthly orders
$monthlyRevenue = $paymentClass->getRevenue($query_vendor_id, $option); // Get monthly revenue
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Analytics Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link rel="stylesheet" href="../../css/analytics_dashboard.css">
    <script src="../../js/analytics_dashboard.js"></script>
</head>

<body class="analytics_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">


        <!--Analytics Dashboard-->
        <div class="container">

            <!-- Text Visualization -->
            <div class="info-section">

                <!--Active Customer-->
                <div class="info-card">
                    <h3>
                        <img src="../../Assets/img/staff.png" alt="active customer icon"
                            style="width:30px; height:auto;margin-right:10px;">
                        Active Customers
                    </h3>
                    <p><?php echo $activeUsers["activeCustomers"]; ?></p>
                </div>

                <!--Active Vendor-->
                <?php if ($role != "Vendor"): ?>
                    <!--Active Vendor-->
                    <div class="info-card">
                        <h3>
                            <img src="../../Assets/img/vendor.png" alt="active vendor icon"
                                style="width:30px; height:auto;margin-right:10px;">
                            Active Vendors
                        </h3>
                        <p><?php echo $activeUsers["activeVendors"]; ?></p>
                    </div>
                <?php endif; ?>

                <!--Number Product-->
                <?php if ($role == "Vendor"): ?>
                    <div class="info-card">
                        <h3>
                            <img src="../../Assets/img/box.png" alt="producticon"
                                style="width:30px; height:auto;margin-right:10px;">
                            Selling Products
                        </h3>
                        <p><?php echo $numberProduct["total_product"]; ?></p>
                    </div>
                <?php endif; ?>

                <!--Refund Percentage-->
                <div class="info-card refund-card">
                    <h3>
                        <img src="../../Assets/img/refund.png" alt="refund percentage icon"
                            style="width:35px; height:auto;margin-right:10px;">
                        Refund Percentage
                    </h3>
                    <p><?php echo $refundPercentage["totalRefundPercentage"]; ?></p>
                </div>
            </div>

            <!-- Top Product -->
            <div class="info-card">
                <h3 class="text-start mb-4">Top Products</h3>

                <?php if (empty($topProduct)): ?>
                    <div
                        style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                        <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                            No product found
                        </div>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Image</th>
                                <th>Product Name</th>
                                <th>Total Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $index = 1;
                            foreach ($topProduct as $product): ?>
                                <tr>
                                    <td><?php echo $index++; ?></td>
                                    <td>
                                        <img src="../../<?php echo $product['product_image']; ?>"
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                             width="100" 
                                             height="100" 
                                             style="border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo (int) $product['total_quantity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>



            <!-- Chart Visualization-->
            <div class="chart-section">

                <!-- Subscription Plan -->
                <?php if ($role != "Vendor"): ?>
                    <div class="chart-container">
                        <h3>Subscription Plans</h3>
                        <div id="subscriptionChart" style="height: 320px; margin-top:30px;"></div>
                    </div>
                <?php endif; ?>

                <!--Shipment Status-->
                <?php if ($role == "Vendor"): ?>
                    <div class="chart-container">
                        <h3>Shipment Status</h3>
                        <div id="shipmentChart" style="height: 320px; margin-top:30px;"></div>
                    </div>
                <?php endif; ?>

                <!-- Payment Method-->
                <div class="chart-container">
                    <h3>Payment Methods</h3>
                    <div id="paymentChart" style="height: 320px; margin-top:30px;"></div>
                </div>

                <!-- Top vendor-->
                <?php if ($role != "Vendor"): ?>
                    <div class="chart-container full-width">
                        <h3>Top Vendors</h3>
                        <div id="vendorChart" style="height: 300px; margin-top:50px;"></div>
                    </div>
                <?php endif; ?>


                <!-- Orders-->
                <div class="chart-container full-width">
                    <h3>Total Orders</h3>

                    <!-- Select duration-->
                    <div class="d-flex justify-content-end align-items-center mb-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                id="ordersFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Filter by <span id="ordersFilterLabel"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="ordersFilterDropdown">
                                <li><a class="dropdown-item" href="#" data-filter="yearly">Year</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="quarterly">Quarter</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="monthly">Month</a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="ordersChart" style="height: 300px; margin-top:50px;"></div>
                </div>

                <!-- Revenue-->
                <div class="chart-container full-width">
                    <h3>Total Sales</h3>
                    <!--Select duration-->
                    <div class="d-flex justify-content-end align-items-center mb-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                id="revenueFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Filter by <span id="revenueFilterLabel"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="revenueFilterDropdown">
                                <li><a class="dropdown-item" href="#" data-filter="yearly">Year<a></li>
                                <li><a class="dropdown-item" href="#" data-filter="quarterly">Quarter</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="monthly">Month</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="revenueChart" style="height: 300px; margin-top:50px;"></div>
                </div>
            </div>
        </div>


        <script id="vendorData" type="application/json">
            <?php echo json_encode($topVendor, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="revenueData" type="application/json">
            <?php echo json_encode($monthlyRevenue, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="ordersData" type="application/json">
            <?php echo json_encode($monthlyOrders, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="subscriptionData" type="application/json">
            <?php echo json_encode($subscriptionData, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="paymentData" type="application/json">
            <?php echo json_encode($topPaymentMethod, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="shipmentData" type="application/json">
            <?php echo json_encode($shipmentStatus, JSON_NUMERIC_CHECK); ?>
        </script>


    </div>

    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

</body>

</html>