<?php
session_start();
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;
    $plan_id = $_POST['plan_id'] ?? null;
    $months = $_POST['subscription_months'] ?? 1;

    if (!$user_id || !$plan_id) {
        echo "Invalid request.";
        exit();
    }

    // Fetch plan details
    $plan_name = getPlanName($conn, $plan_id);
    if (!$plan_name) {
        echo "Subscription plan not found.";
        exit();
    }

    // Calculate subscription end date
    $end_date = date("Y-m-d", strtotime("+$months months"));

    // Check if user is already a vendor
    $is_vendor = checkIfVendor($conn, $user_id);

    if (!$is_vendor) {
        // Upgrade user to vendor
        $success = upgradeToVendor($conn, $user_id, $plan_id, $end_date);
    } else {
        // Update vendor subscription
        $success = updateVendorSubscription($conn, $user_id, $plan_id, $end_date);
    }

    if ($success) {
        // Convert plan_id to tier string
        $planParam = ($plan_id == 1) ? "tier1" : (($plan_id == 2) ? "tier2" : "tier3");

        // Redirect with parameters
        header("Location: ../Modules/vendor/subscription_listing.php?subscribe=success&plan=$planParam&months=$months");
        exit();
    } else {
        echo "Error Subscribing Plan.";
    }
}
?>
