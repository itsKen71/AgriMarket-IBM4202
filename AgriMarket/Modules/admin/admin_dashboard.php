<?php
session_start();

include '../../includes/database.php';

//Fetch Vendor List
$vendorList = getVendorList();

//Fetch Staff List
$staffList = getStaffList();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/admin_dashboard.css">

</head>

<body class="admin_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

        <!--Vendor Listing---------------------------------------->
        <div class="Vendor-Listing">
            <h1>Vendor Listing</h1>

            <div class="Vendor-Listing-Container">
                <?php if (!empty($vendorList)): ?>
                    <?php foreach ($vendorList as $vendor): ?>

                        <!--Vendor Card-->
                        <div class="Vendor-Card">

                            <!--Header Section(Display Store Name)--->
                            <div class="Vendor-Listing-Container-Header">
                                <h2><?= htmlspecialchars($vendor['store_name']); ?></h2>
                            </div>

                            <!--Content Section(Display Plan, Subscription Expiration, Assisstance)-->
                            <div class="Vendor-Listing-Container-Content">
                                <p>Subscription Type: <?= $vendor['plan_name']; ?></p>
                                <p>Expiration Date: <?= $vendor['subscription_end_date']; ?></p>
                                <p>Staff Assisstance: <?= ($vendor['staff_assisstance'] != '-') ? $vendor['staff_assisstance'] : "No Staff Assigned"; ?></p>
                            </div>

                            <!--Button for Add Assigned Assistance-->
                            <div class="Vendor-Listing-Container-Footer">
                                <img src="../../Assets/img/addAssistanceBTN.png" alt="Add Assistance Button" style="width:50px; height:auto;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>---No Vendor Found---</h3>
                <?php endif; ?>
            </div>
        </div>

        <!--Staff Listing------------------------------------------->
        <div class="Staff-Listing">
            <h1>Staff Listing</h1>
            <a href="../../Modules/authentication/sign_up.php">
                <img src="../../Assets/img/addStaff.png" alt="Add Staff Button" style="width:50px; height:auto;">
            </a>

            <div class="Staff-Listing-Container">
                <?php if (!empty($staffList)): ?>
                    <?php foreach ($staffList as $staff): ?>

                        <!--Staff Card-->
                        <div class="Staff-Card">

                            <!--Header SAection(Display Staff Name)-->
                            <div class="Staff-Listing-Container-Header">
                                <h2><?= htmlspecialchars($staff['Name']); ?></h2>
                            </div>

                            <!--Content Section(Display Last Online, Performance Tracking)-->
                            <p>Last Online: <?= $staff['last_online']; ?></p>

                            <!--Performance Tracking-->
                            <!---->
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>---No Staff Found---</h3>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>