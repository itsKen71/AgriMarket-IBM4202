<?php
session_start();
include '../../includes/database.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php"); // Redirect to login page
    exit();
}

$vendor = getVendorDetails($user_id, $conn);

if (!$vendor) {
    echo "Error: Vendor profile not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Vendor Profile</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link rel="stylesheet" href="../../css/vendor_profile.css">
    <script src="../../js/vendor_profile.js"></script>
</head>

<body class="vendor_profile" data-edit-success="<?= isset($_GET['update']) && $_GET['update'] == 'success' ? 'true' : 'false'; ?>">
    <?php include '../../includes/header.php'; ?>
    
    <div class="container mt-5">
        <!-- Content Start Here -->
        <h2 class="mb-5">Vendor Profile</h2> 
        <?php if ($vendor): ?>
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../../assets/svg/person-circle.svg" alt="Vendor Icon" class="profile-icon">
                    <p class="store-name"><?= htmlspecialchars($vendor['store_name']); ?></p>          
                </div>

                <div class="profile-details mt-3">
                    <p><strong>Email:</strong> <?= htmlspecialchars($vendor['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($vendor['phone_number']); ?></p>
                    <p><strong>Subscription Plan:</strong> <?= htmlspecialchars($vendor['plan_name'] ?? 'N/A'); ?></p>
                    <p><strong>Subscription Start:</strong> <?= htmlspecialchars($vendor['subscription_start_date']); ?></p>
                    <p><strong>Subscription End:</strong> <?= htmlspecialchars($vendor['subscription_end_date']); ?></p>
                </div>
                
                <!-- Request Assistance Button -->
                <?php if (!empty($vendor['staff_assisstance_id']) && $vendor['has_staff_support'] == 1): ?>
                    <button class="btn btn-primary request-assistance-btn" data-bs-toggle="modal" data-bs-target="#requestAssistanceModal">
                        Request Assistance
                    </button>
                <?php endif; ?>


                <button class="btn btn-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editModal">Edit Store Profile</button>
            </div>
        <?php else: ?>
            <p>No vendor profile found. Please complete your registration.</p>
        <?php endif; ?>
    </div>

    <!-- Edit Vendor Profile -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Vendor Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Edit Form -->
                    <form action="../../includes/edit_vendor_profile.php" method="POST">
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Store Name</label>
                            <input type="text" class="form-control" id="store_name" name="store_name" value="<?= htmlspecialchars($vendor['store_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($vendor['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($vendor['phone_number']); ?>" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Assistance Modal -->
     <div class="modal fade" id="requestAssistanceModal" tabindex="-1" aria-labelledby="requestAssistanceModalLabel" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestAssistanceModalLabel">Request Assistance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <form action="../../includes/submit_request.php" method="POST">
                            <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id']); ?>">

                            <div class="mb-3">
                                <label for="request_type" class="form-label">Request Type</label>
                                <select class="form-select" id="request_type" name="request_type" required>
                                    <option value="Feature Request">Feature Request</option>
                                    <option value="Account Issue">Account Issue</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Billing Inquiry">Billing Inquiry</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                </select>
                            </div>
                            
                    <div class="mb-3">
                        <label for="request_description" class="form-label">Description</label>
                        <textarea class="form-control" id="request_description" name="request_description" rows="8" required></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    <!-- Edit Success Modal -->
    <div class="modal fade" id="editSuccessModal" tabindex="-1" aria-labelledby="editSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSuccessModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    Vendor profile updated successfully!
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Success Modal -->
     <div class="modal fade" id="requestSuccessModal" tabindex="-1" aria-labelledby="requestSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestSuccessModalLabel">Request Submitted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            
                <div class="modal-body">
                    Your assistance request has been successfully submitted!
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
