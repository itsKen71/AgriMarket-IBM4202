<?php
session_start();
include '../../includes/database.php';

// Fetch Data for Analytics Dashboard
$activeUsers = getActiveUser($conn);
$refundPercentage = getRefundPercentage($conn);
$monthlyRevenue = getRevenue($conn);
$monthlyOrders = getOrders($conn);
$subscriptionData = getSubscription($conn);
$topFiveCategory = getTopFiveCategory($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Analytics Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/temp-logo.png">
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
                    <h3>Total Active Customers</h3>
                    <p><?php echo $activeUsers["activeCustomers"]; ?></p>
                </div>

                <!--Active Vendor-->
                <div class="info-card">
                    <h3>Total Active Vendors</h3>
                    <p><?php echo $activeUsers["activeVendors"]; ?></p>
                </div>

                <!--Refund Percentage-->
                <div class="info-card refund-card">
                    <h3>Refund Percentage</h3>
                    <p><?php echo $refundPercentage["totalRefundPercentage"]; ?>%</p>
                </div>
            </div>

            <!-- Chart Visualization-->
            <div class="chart-section">
                <!-- Subscription Plan -->
                <div class="chart-container">
                    <h3>Subscription Plans</h3>
                    <div id="subscriptionChart" style="height: 320px; margin-top:30px;"></div>
                </div>

                <!-- Top 5 Product Category-->
                <div class="chart-container">
                    <h3>Top 5 Product Categories</h3>
                    <div id="categoryChart" style="height: 320px; margin-top:30px;"></div>
                </div>

                <!-- Orders-->
                <div class="chart-container full-width">
                    <h3>Total Orders</h3>
                    <div id="ordersChart" style="height: 300px; margin-top:50px;"></div>
                </div>

                <!-- Revenue-->
                <div class="chart-container full-width">
                    <h3>Total Revenue</h3>
                    <div id="revenueChart" style="height: 300px; margin-top:50px;"></div>
                </div>
            </div>
        </div>

        <!-- Embed JSON Data for JS -->
        <script id="revenueData" type="application/json">
            <?php echo json_encode($monthlyRevenue, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="ordersData" type="application/json">
            <?php echo json_encode($monthlyOrders, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="subscriptionData" type="application/json">
            <?php echo json_encode($subscriptionData, JSON_NUMERIC_CHECK); ?>
        </script>
        <script id="categoryData" type="application/json">
            <?php echo json_encode($topFiveCategory, JSON_NUMERIC_CHECK); ?>
        </script>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</body>

</html>