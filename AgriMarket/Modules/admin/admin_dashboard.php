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
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <link rel="stylesheet" href="../../css/admin_dashboard.css">
    <script src="../../js/admin_dashboard.js"></script>

</head>

<body class="admin_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

        <!--Listing-->
        <div class="accordion" id="accordionPanels">

            <!--Vendor Listing-->
            <div class="accordion-item">
                <!--Header-->
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        <strong>Vendor Listing</strong>
                    </button>
                </h2>

                <!--Toggle Area-->
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">

                    <!--Pending Card-->
                    <div class="accordion-body">
                        <?php if (!empty($vendorList)): ?>
                            <?php foreach ($vendorList as $vendor): ?>

                                <!--Vendor Card-->
                                <div class="Vendor-Card">

                                    <!--Header Section(Display Store Name)--->
                                    <div class="Vendor-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($vendor['store_name']); ?></h2>
                                    </div>

                                    <div class="Vendor-Card-Body">
                                        <div class="Vendor-Listing-Container-Content">
                                            <span class="label">Subscription Type</span> <span class="colon">:</span> <?= $vendor['plan_name']; ?>
                                            <span class="label">Expiration Date</span> <span class="colon">:</span> <?= $vendor['subscription_end_date']; ?>
                                            <span class="label">Staff Assistance</span> <span class="colon">:</span> <?= $vendor['staff_assistance']; ?>
                                        </div>

                                        <!--Button for Assign Assistance(Tier 3 )-->
                                        <?php if ($vendor['plan_name'] == 'Tier_III'): ?>
                                            <div class="Vendor-Listing-Container-Button">
                                                <img src="../../Assets/img/edit.png" alt="Add Assistance Button"
                                                    data-vendor-id="<?= htmlspecialchars($vendor['vendor_id']); ?>"
                                                    data-store="<?= htmlspecialchars($vendor['store_name']); ?>"
                                                    data-subscription-type="<?= htmlspecialchars($vendor['plan_name']); ?>"
                                                    data-expiration-date="<?= htmlspecialchars($vendor['subscription_end_date']); ?>"
                                                    data-assistance-name="<?= htmlspecialchars($vendor['staff_assistance']); ?>"
                                                    data-assistance-id="<?= htmlspecialchars($vendor['staff_assisstance_id']); ?>"
                                                    onclick="editVendorListing(this)">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data">
                                <p>---No Vendor Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editVendorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-center w-100">Edit Vendor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <p><strong>Store Name</strong> <br><span id="show-store"></span></p>
                            <p><strong>Subscription Type</strong><br> <span id="show-subscription"></span></p>
                            <p><strong>Subscription Expiration</strong> <br><span id="show-expiration"></span></p>
                            <p><strong>Current Assistance</strong><br> <span id="show-staff"></span></p>

                            <!-- Staff selection -->
                            <div id="staff-selection-container">
                                <p><strong>New Assistance</strong></p>
                                <select id="staff-select" class="form-control">
                                    <?php foreach ($staffList as $staff): ?>
                                        <option value="<?= $staff['user_id']; ?>">
                                            <?= $staff['user_id'] . " - " . $staff['Name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="updateVendor()">Update</button>
                        </div>
                    </div>
                </div>
            </div>

            <!--Staff Listing-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        <strong>Staff Listing</strong>
                        <a href="../../Modules/authentication/sign_up.php">
                            <img src="../../Assets/img/addStaff.png" alt="Add Staff Button" style="width: 25px; height: 25px;">
                        </a>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <?php if (!empty($staffList)): ?>
                            <?php foreach ($staffList as $staff): ?>

                                <!--Staff Card-->
                                <div class="Staff-Card">

                                    <!--Header Section(Display Staff Name)-->
                                    <div class="Staff-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($staff['Name']); ?></h2>
                                    </div>

                                    <div class="Staff-Card-Body">
                                        <div class="Staff-Listing-Container-Content">
                                            <!--Content Section(Display Last Online, Performance Tracking)-->
                                            <span class="label">Last Online</span> <span class="colon">:</span> <?= $staff['last_online']; ?>
                                            <span class="label">Total Request Received</span> <span class="colon">:</span> <?= $staff['totalRequest']; ?>
                                            <span class="label">Total Request Solved</span> <span class="colon">:</span> <?= $staff['totalCompleted']; ?>
                                            <span class="label">Performance</span> <span class="colon">:</span>

                                            <?php
                                            $progress = round($staff['progress_percentage'] ?? 0);
                                            ?>

                                            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-success" style="width: <?= $progress ?>%;">
                                                    <?= $progress ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data">
                                <p>---No Staff Found---</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>