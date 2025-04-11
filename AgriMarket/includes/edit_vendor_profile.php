<?php
session_start();
include 'database.php';

$db = new Database();
$vendorClass = new Vendor($db);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = htmlspecialchars(trim($_POST['store_name']));

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        header("Location: ../../Modules/authentication/login.php");
        exit();
    }

    $vendor = $vendorClass->getVendorDetails($user_id);
    if ($vendor) {
        $vendor_id = $vendor['vendor_id'];

        // Update the vendor store name
        $storeUpdateSuccess = $vendorClass->updateVendorProfile($store_name, $vendor_id);

        if ($storeUpdateSuccess) {
            // Redirect on success
            header("Location: ../Modules/vendor/vendor_profile.php?update=success");
            exit();
        } else {
            // If store name update fails
            header("Location: ../Modules/vendor/vendor_profile.php?update=error");
            exit();
        }
    } else {
        // Vendor not found
        header("Location: ../Modules/vendor/vendor_profile.php?update=error");
        exit();
    }
}
?>
