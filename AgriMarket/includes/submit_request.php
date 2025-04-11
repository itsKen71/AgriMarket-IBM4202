<?php
session_start();
include 'database.php';

$db = new Database();
$vendorClass = new Vendor($db);


$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../Modules/authentication/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_type = htmlspecialchars(trim($_POST['request_type']));
    $request_description = htmlspecialchars(trim($_POST['request_description']));

    $vendor = $vendorClass->getVendorDetails($user_id);

    if ($vendor && !empty($vendor['vendor_id'])) {
        $vendor_id = $vendor['vendor_id'];

        // Insert the request into the database
        $requestSuccess = $vendorClass->insertRequest($vendor_id, $request_type, $request_description);

        if ($requestSuccess) {
            header("Location: ../Modules/vendor/vendor_profile.php?request=success");
        } else {
            header("Location: ../Modules/vendor/vendor_profile.php?request=error");
        }
    } else {
        header("Location: ../Modules/vendor/vendor_profile.php?request=error");
    }
}
?>