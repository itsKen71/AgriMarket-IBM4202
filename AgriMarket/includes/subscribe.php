<?php
session_start();
include 'database.php';

$db = new Database();
$customerClass = new Customer($db);
$vendorClass = new Vendor($db);
$adminClass = new Admin($db);



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;
    $plan_id = $_POST['plan_id'] ?? null;
    $months = $_POST['subscription_months'] ?? 1200;

    if (!$user_id || !$plan_id) {
        echo "Invalid request.";
        exit();
    }

    // Fetch plan details
    $plan_name = $vendorClass->getPlanName($plan_id);
    if (!$plan_name) {
        echo "Subscription plan not found.";
        exit();
    }

    // Calculate subscription end date
    $end_date = date("Y-m-d", strtotime("+$months months"));

    // Check if user is already a vendor
    $is_vendor = $vendorClass->checkIfVendor($user_id);

    if (!$is_vendor) {
        // Upgrade user to vendor
        $success = $customerClass->upgradeToVendor($user_id, $plan_id, $end_date);
        if ($success) {
            $_SESSION['role'] = 'Vendor'; 
        }
    } else {
        // Update vendor subscription
        $success = $vendorClass->updateVendorSubscription($user_id, $plan_id, $end_date);
    }

    if ($success) {
        // Convert plan_id to tier string
        $planParam = ($plan_id == 1) ? "tier1" : (($plan_id == 2) ? "tier2" : "tier3");

        // Redirect with parameters
        header("Location: ../Modules/vendor/product_listings.php?subscribe=success&plan=$planParam&months=$months");
        exit();
    } else {
        echo "Error Subscribing Plan.";
    }
}
?>
