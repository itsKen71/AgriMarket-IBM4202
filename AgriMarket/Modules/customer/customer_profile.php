<?php
session_start();
include '../../includes/database.php'; // Include the database connection file

// Initialize database and class objects
$db = new Database();
$userClass = new User($db);
$customerClass = new Customer($db);
$productClass = new Product($db);
$paymentClass = new Payment($db);

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'] ?? null;

// Redirect to the login page if the user is not logged in
if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

// Fetch customer details using the user ID
$customer = $customerClass->getCustomerDetails($user_id);

// Fetch the user's profile image using the user ID
$user_image = $userClass->getUserImageFromUserID(user_id: $user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Customer Profile</title>
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <link rel="stylesheet" href="../../css/customer_profile.css">
    <script src="../../js/customer_profile.js"></script>
</head>

<body class="customer_profile">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <?php if (isset($_GET['update'])): ?>
            <?php if ($_GET['update'] === 'success'): ?>
                <div class="alert alert-success text-center">Profile updated successfully!</div>
            <?php elseif ($_GET['update'] === 'error'): ?>
                <div class="alert alert-danger text-center">Failed to update profile. Please try again.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-header">
                <img src="../../<?= htmlspecialchars($user_image); ?>" alt="Customer Icon"
                    class="profile-icon rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                <p class="customer-name">
                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                </p>
            </div>

            <div class="profile-details mt-3">
                <p><strong>Username:</strong> <span><?= htmlspecialchars($customer['username']); ?></span></p>
                <p><strong>Email:</strong> <span><?= htmlspecialchars($customer['email']); ?></span></p>
                <p><strong>Phone Number:</strong> <span><?= htmlspecialchars($customer['phone_number']); ?></span></p>
                <p><strong>Address:</strong> <span><?= htmlspecialchars($customer['home_address']); ?></span></p>
            </div>

            <button class="btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal">Edit User Info</button>
        </div>

        <div class="tracking-card mt-5">
            <h3 class="text-center">Track Your Order</h3>
            <form action="" method="POST" class="tracking-form mt-3">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="tracking_number" placeholder="Enter Tracking Number"
                        required>
                    <button class="btn btn-success" type="submit">Track</button>
                </div>
            </form>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tracking_number'])): ?>
                <?php
                $tracking_number = htmlspecialchars(trim($_POST['tracking_number']));
                $shipment = $productClass->getShipmentDetails($tracking_number, $user_id);

                if ($shipment) {
                    if ($shipment['status'] === 'Cancelled') {
                        // Display alert if the shipment is cancelled
                        echo '<div class="alert alert-danger text-center mt-3">This shipment has been refunded or cancelled.</div>';
                        return; // Stop further processing if the shipment is cancelled
                    }

                    $start_date = strtotime($shipment['update_timestamp']);
                    $end_date = strtotime($shipment['estimated_delivery_date']);
                    $current_date = time();

                    // Calculate progress
                    $progress = min(100, max(0, (($current_date - $start_date) / ($end_date - $start_date)) * 100));

                    if ($progress >= 100) {
                        $new_status = 'Delivered';
                    } elseif ($progress >= 50) {
                        $new_status = 'Ready to Pickup by Carrier';
                    } elseif ($progress >= 30) {
                        $new_status = 'Packaging';
                    } else {
                        $new_status = 'Pending';
                    }

                    if ($shipment['status'] !== $new_status) {
                        $productClass->updateShipmentStatus($shipment['shipping_id'], $new_status);
                    }

                    $status = $new_status;
                    $status_text = [
                        'Pending' => 'Pending',
                        'Packaging' => 'Packaging',
                        'Ready to Pickup by Carrier' => 'Ready to Pickup by Carrier',
                        'Delivered' => 'Arrived'
                    ];
                }
                ?>
                <?php if ($shipment): ?>
                    <div class="progress mt-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress; ?>%;"
                            aria-valuenow="<?= $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?= $status_text[$status] ?? 'In Progress'; ?>
                        </div>
                    </div>
                    <p class="text-center mt-2">Estimated Delivery Date:
                        <?= htmlspecialchars($shipment['estimated_delivery_date']); ?>
                    </p>
                <?php elseif (!$shipment): ?>
                    <div class="alert alert-danger text-center mt-3">Invalid tracking number or no order found for this user.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Customer Info Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Customer Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../includes/edit_customer_profile.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?= htmlspecialchars($customer['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?= htmlspecialchars($customer['first_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?= htmlspecialchars($customer['last_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($customer['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                value="<?= htmlspecialchars($customer['phone_number']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="home_address" class="form-label">Home Address</label>
                            <textarea class="form-control" id="home_address" name="home_address" rows="3"
                                required><?= htmlspecialchars($customer['home_address']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image"
                                accept="image/*">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>