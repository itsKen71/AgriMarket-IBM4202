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
    <script src="../../js/admin_dashboard.js"></script>

</head>

<body class="admin_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

        <!--Vendor Listing---------------------------------------->
        <div class="Vendor-Listing">
            <h1>Vendor Listing
                <button type="button" id="toggleVendor">▼</button>
            </h1>

            <!--Collapse Area-->
            <div id="vendorList" class="show">
                <div class="Vendor-Listing-Container">
                    <?php if (!empty($vendorList)): ?>
                        <?php foreach ($vendorList as $vendor): ?>

                            <!--Vendor Card-->
                            <div class="Vendor-Card">

                                <!--Header Section(Display Store Name)--->
                                <div class="Vendor-Listing-Container-Header">
                                    <h2><?= htmlspecialchars($vendor['store_name']); ?></h2>
                                </div>

                                <div class="Listing-Container-Aligned">
                                    <!--Content Section(Display Plan, Subscription Expiration, Assisstance)-->
                                    <div class="Vendor-Listing-Container-Content">
                                        <span class="label">Subscription Type</span> <span class="colon">:</span> <?= $vendor['plan_name']; ?>
                                        <span class="label">Expiration Date</span> <span class="colon">:</span> <?= $vendor['subscription_end_date']; ?>
                                        <span class="label">Staff Assistance</span> <span class="colon">:</span> <?= $vendor['staff_assistance']; ?>
                                    </div>

                                    <!--Button for Add Assigned Assistance-->
                                    <div class="Vendor-Listing-Container-Footer">
                                        <img src="../../Assets/img/edit.png" alt="Add Assistance Button" 
                                            get-data-store="<?= htmlspecialchars($vendor['store_name']); ?>"
                                            get-subscription-type="<?= htmlspecialchars($vendor['plan_name']); ?>"
                                            get-data-expiration_date="<?= htmlspecialchars($vendor['subscription_end_date']); ?>"
                                            get-staff-assistance="<?= htmlspecialchars($vendor['staff_assistance']); ?>"
                                            onclick="editVendorListing(this)"
                                        >
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <h3>---No Vendor Found---</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!--Staff Listing------------------------------------------->
        <div class="Staff-Listing">
            <h1 class="Staff-Heading">
                <span class="Staff-Heading-Title">Staff Listing</span>
                <div class="add-staff">
                    <a href="../../Modules/authentication/sign_up.php">
                        <img src="../../Assets/img/addStaff.png" alt="Add Staff Button">
                    </a>
                </div>
                <button type="button" id="toggleStaff">▼</button>
            </h1>

            <div id="staffListing" class="collapse show">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>