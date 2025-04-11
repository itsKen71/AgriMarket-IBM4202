<?php
session_start();
include 'database.php';

$db = new Database();
$userClass = new User($db);
$customerClass = new Customer($db);
$vendorClass = new Vendor($db);


$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../Modules/authentication/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone_number = htmlspecialchars(trim($_POST['phone_number']));
    $home_address = htmlspecialchars(trim($_POST['home_address']));
    $profile_image = $_FILES['profile_image'];

    if (empty($username) || empty($first_name) || empty($last_name) || !$email || empty($phone_number) || empty($home_address)) {
        $redirect_url = $vendorClass->isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=error" : "../Modules/customer/customer_profile.php?update=error";
        header("Location: $redirect_url");
        exit();
    }

    $image_path = null;
    if (!empty($profile_image['name'])) {
        $upload_dir = realpath(__DIR__ . "/../../AgriMarket/Assets/img/profile_img/") . "/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = basename($profile_image['name']);
        $target_file = $upload_dir . $image_name;
        $image_path = "Assets/img/profile_img/" . $image_name;

        if (!move_uploaded_file($profile_image['tmp_name'], $target_file)) {
            header("Location: ../Modules/customer/customer_profile.php?update=error");
            exit();
        }
    }

    // Use the function from database.php to update the profile
    if ($customerClass->updateCustomerInfo($user_id, $username, $first_name, $last_name, $email, $phone_number, $home_address, $image_path)) {
        if ($vendorClass->isVendor($user_id)) {
            $redirect_url = "../Modules/vendor/vendor_profile.php?update=success";
        } elseif ($userClass->getRole($user_id) === 'Staff') {
            $redirect_url = "../Modules/staff/staff_profile.php?update=success";
        } elseif ($userClass->getRole($user_id) === 'Admin') {
            $redirect_url = "../Modules/admin/admin_profile.php?update=success";
        } else {
            $redirect_url = "../Modules/customer/customer_profile.php?update=success";
        }
        header("Location: $redirect_url");
    } else {
        if ($vendorClass->isVendor($user_id)) {
            $redirect_url = "../Modules/vendor/vendor_profile.php?update=error";
        } elseif ($userClass->getRole($user_id) === 'Staff') {
            $redirect_url = "../Modules/staff/staff_profile.php?update=error";
        } elseif ($userClass->getRole($user_id) === 'Admin') {
            $redirect_url = "../Modules/admin/admin_profile.php?update=error";
        } else {
            $redirect_url = "../Modules/customer/customer_profile.php?update=error";
        }
        header("Location: $redirect_url");
    }

    $conn->close();
} else {
    $redirect_url = $vendorClass->isVendor($user_id) ? "../Modules/vendor/vendor_profile.php" : "../Modules/customer/customer_profile.php";
    header("Location: $redirect_url");
    exit();
}