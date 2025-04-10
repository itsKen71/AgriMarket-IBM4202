<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

include '../../includes/database.php';

//Fetch Vendor List
$vendorList = getVendorList();
//Fetch Staff List
$staffList = getStaffList();
//Fetch admin list
$adminList = getAdminList();

//Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['vendor_id'], $_POST['staff_id'])) {
    $vendorId = $_POST['vendor_id'];
    $staffId = $_POST['staff_id'];

    $result = updateVendorAssistance($vendorId, $staffId);

    header('Content-Type: application/json');
    echo json_encode(["status" => $result ? "success" : "error"]);
    exit();
}

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

        <?php if (isset($_GET['success']) && $_GET['success'] === 'staff_added'): ?>
            <div class="alert alert-success">Staff/Admin added successfully!</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'password_mismatch'): ?>
            <div class="alert alert-danger">Passwords do not match.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate_entry'): ?>
            <div class="alert alert-danger">The username, email, or phone number is already registered.</div>
        <?php endif; ?>

        <!--Add admin/ staff-->
        <div class="addRole">
            <p>Add Admin/ Staff
                <img src="../../Assets/img/addStaff.png" alt="Add Role Button" style="width:30px; height:auto;cursor:pointer;" class="addRoleBTN" data-bs-toggle="modal" data-bs-target=#addStaffModal>
            </p>
        </div>

        <!--Listing-->
        <div class="accordion" id="accordionPanels">

            <!--Vendor Listing-->
            <div class="accordion-item">
                <!--Header-->
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        <img src="../../Assets/img/vendor.png" alt="vendor icon" style="width:30px; height:auto;margin-right:10px;">
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
                                <div class="Vendor-Card" data-vendor-id="<?= htmlspecialchars($vendor['vendor_id']); ?>">
                                    <!-- Header Section(Display Store Name) -->
                                    <div class="Vendor-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($vendor['store_name']); ?></h2>
                                    </div>

                                    <div class="Vendor-Card-Body">
                                        <div class="Vendor-Listing-Container-Content">
                                            <span class="label">Subscription Type</span> <span class="colon">:</span> <?= $vendor['plan_name']; ?>
                                            <span class="label">Expiration Date</span> <span class="colon">:</span> <?= $vendor['subscription_end_date']; ?>
                                            <span class="label">Staff Assistance</span> <span class="colon">:</span>
                                            <span class="staff-assistance"><?= $vendor['staff_assistance']; ?></span>
                                        </div>

                                        <!-- Button for Assign Assistance (Tier 3) -->
                                        <?php if ($vendor['plan_name'] == 'Tier_III'): ?>
                                            <div class="Vendor-Listing-Container-Button">
                                                <img src="../../Assets/img/edit.png" alt="Add Assistance Button"
                                                    data-vendor-id="<?= htmlspecialchars($vendor['vendor_id']); ?>"
                                                    data-store="<?= htmlspecialchars($vendor['store_name']); ?>"
                                                    data-subscription-type="<?= htmlspecialchars($vendor['plan_name']); ?>"
                                                    data-expiration-date="<?= htmlspecialchars($vendor['subscription_end_date']); ?>"
                                                    data-assistance-name="<?= htmlspecialchars($vendor['staff_assistance']); ?>"
                                                    data-assistance-id="<?= htmlspecialchars($vendor['assist_by']); ?>"
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

            <!-- Modal for Editing Vendor Assistance -->
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
                                        <option value="<?= $staff['user_id']; ?>" data-name="<?= htmlspecialchars($staff['Name']); ?>">
                                            User ID <?= $staff['user_id'] . " - " . $staff['Name']; ?>
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

            <!--Admin Listing-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                        <img src="../../Assets/img/admin.png" alt="vendor icon" style="width:30px; height:auto; margin-right:10px">
                        <strong>Admin Listing</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">

                        <?php if (!empty($adminList)): ?>
                            <?php foreach ($adminList as $admin): ?>
                                <!-- Admin Card -->
                                <div class="Admin-Card">
                                    <!-- Header Section (Display Admin Name) -->
                                    <div class="Admin-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($admin['Name']); ?></h2>
                                    </div>

                                    <div class="Admin-Card-Body">
                                        <div class="Admin-Listing-Container-Content">
                                            <!-- Content Section -->
                                            <span class="label">Phone Number</span> <span class="colon">:</span> <?= $admin['phone_number']; ?>
                                            <span class="label">Home Address</span> <span class="colon">:</span> <?= $admin['home_address']; ?>
                                            <span class="label">Last Online</span> <span class="colon">:</span> <?= $admin['last_online']; ?>

                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data">
                                <p>---No Admin Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!--Staff Listing-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        <img src="../../Assets/img/staff.png" alt="staff icon" style="width:30px; height:auto; margin-right:10px">
                        <strong>Staff Listing</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">

                        <?php if (!empty($staffList)): ?>
                            <?php foreach ($staffList as $staff): ?>
                                <!-- Staff Card -->
                                <div class="Staff-Card">
                                    <!-- Header Section (Display Staff Name) -->
                                    <div class="Staff-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($staff['Name']); ?></h2>
                                    </div>

                                    <div class="Staff-Card-Body">
                                        <div class="Staff-Listing-Container-Content">
                                            <!-- Content Section (Display Last Online, Performance Tracking) -->
                                            <span class="label">Phone Number</span> <span class="colon">:</span> <?= $staff['phone_number']; ?>
                                            <span class="label">Home Address</span> <span class="colon">:</span> <?= $staff['home_address']; ?>
                                            <span class="label">Last Online</span> <span class="colon">:</span> <?= $staff['last_online']; ?>
                                            <span class="label">Performance</span> <span class="colon">:</span>

                                            <?php
                                            $progress = round($staff['progress_percentage'] ?? 0);
                                            ?>

                                            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="max-width: 150px;">
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

    <!-- Add Staff/Admin Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">Add Admin/Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm" action="../../includes/add_staff_admin.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="home_address" class="form-label">Home Address</label>
                            <textarea class="form-control" id="home_address" name="home_address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>