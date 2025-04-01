<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = $_POST['store_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $vendor_id = 1; // temporary use for testing
    //$vendor_id = $_SESSION['vendor_id'] ?? null

    if (!$vendor_id) {
        header("Location: ../../authentication/login.php");
        exit();
    }

    // Fetch the current user_id for the vendor
    $vendor = getVendorDetails($vendor_id, $conn);
    $user_id = $vendor['user_id'];

    // Update vendor store name
    $stmt = $conn->prepare("UPDATE vendor SET store_name = ? WHERE vendor_id = ?");
    $stmt->bind_param("si", $store_name, $vendor_id);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("UPDATE user SET email = ?, phone_number = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $email, $phone_number, $user_id);

        if ($stmt->execute()) {
            // Redirect on success
            header("Location: ../Modules/vendor/vendor_profile.php?update=success");
            exit();
        } else {
            // If email and phone update fails
            echo "Error updating email and phone: " . $stmt->error;
        }
    } else {
        // If store name update fails
        echo "Error updating store name: " . $stmt->error;
    }
}
?>
