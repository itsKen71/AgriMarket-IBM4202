<?php
session_start();
include 'database.php';

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

    if (empty($username) || empty($first_name) || empty($last_name) || !$email || empty($phone_number) || empty($home_address)) {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=error" : "../Modules/customer/customer_profile.php?update=error";
        header("Location: $redirect_url");
        exit();
    }

    $updateSuccess = updateCustomerProfile($conn, $user_id, $username, $first_name, $last_name, $email, $phone_number, $home_address);

    if ($updateSuccess) {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=success" : "../Modules/customer/customer_profile.php?update=success";
        header("Location: $redirect_url");
    } else {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=error" : "../Modules/customer/customer_profile.php?update=error";
        header("Location: $redirect_url");
    }

    $conn->close();
} else {
    $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php" : "../Modules/customer/customer_profile.php";
    header("Location: $redirect_url");
    exit();
}