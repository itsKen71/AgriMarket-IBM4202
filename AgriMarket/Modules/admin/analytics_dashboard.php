<?php
session_start();
include '../../includes/database.php';

//Get user id and role from the session
/////////////////////////////Dummy Function/////////////////////////////
$role = "Admin";
//Assign value -1 if role is admin,if not as usual id assignment
$user_id = ($role == "Admin") ? -1 : $_SESSION['user_id'];


// Fetch data based on specific role
$activeUsers = getActiveUser($conn);
$refundPercentage = getRefundPercentage($conn, $user_id);
$monthlyRevenue = getRevenue($conn,$user_id);
$monthlyOrders = getOrders($conn,$user_id);
$subscriptionData = getSubscription($conn);
$topFiveCategory = getTopFiveProduct($conn,$user_id);

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
                    <h3>Refund Percentage (%)</h3>
                    <p><?php echo $refundPercentage["totalRefundPercentage"]; ?></p>
                </div>
            </div>

            <!-- Chart Visualization-->
            <div class="chart-section">
                <!-- Subscription Plan -->
                <div class="chart-container">
                    <h3>Subscription Plans</h3>
                    <div id="subscriptionChart" style="height: 320px; margin-top:30px;"></div>
                </div>

                <!-- Top 5 Product List-->
                <div class="chart-container">
                    <h3>Top 5 Product List</h3>
                    <div id="categoryChart" style="height: 320px; margin-top:30px;"></div>
                </div>

                <!-- Orders-->
                <div class="chart-container full-width">
                    <h3>Total Orders (in year 2025)</h3>
                    <div id="ordersChart" style="height: 300px; margin-top:50px;"></div>
                </div>

                <!-- Revenue-->
                <div class="chart-container full-width">
                    <h3>Total Revenue (in year 2025)</h3>
                    <div id="revenueChart" style="height: 300px; margin-top:50px;"></div>
                </div>
            </div>
        </div>

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

    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</body>

</html>