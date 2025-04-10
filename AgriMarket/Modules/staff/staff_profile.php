<?php
session_start();
include '../../includes/database.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

$customer = getCustomerDetails($user_id, $conn);
$user_image = getUserImageFromUserID(user_id: $user_id);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Staff Profile</title>
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
    </div>

    <!-- Edit Staff Info Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Staff Info</h5>
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

    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>