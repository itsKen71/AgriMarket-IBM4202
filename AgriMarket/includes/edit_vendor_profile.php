<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = $_POST['store_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        header("Location: ../../Modules/authentication/login.php");
        exit();
    }

    $vendor = getVendorDetails($user_id, $conn);
    if ($vendor) {
        $vendor_id = $vendor['vendor_id'];

        // Update the vendor store name
        $storeUpdateSuccess = updateVendorProfile($conn, $store_name, $vendor_id);

        if ($storeUpdateSuccess) {
            // Update the user email and phone number
            $userUpdateSuccess = updateUserDetails($conn, $email, $phone_number, $user_id);

            if ($userUpdateSuccess) {
                // Redirect on success
                header("Location: ../Modules/vendor/vendor_profile.php?update=success");
                exit();
            } else {
                // If user details update fails
                echo "Error updating email and phone";
            }
        } else {
            // If store name update fails
            echo "Error updating store name";
        }
    } else {
        echo "Vendor not found.";
    }
}
?>
