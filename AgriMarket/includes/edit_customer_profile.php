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
    $profile_image = $_FILES['profile_image'];

    if (empty($username) || empty($first_name) || empty($last_name) || !$email || empty($phone_number) || empty($home_address)) {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=error" : "../Modules/customer/customer_profile.php?update=error";
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

    

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE user SET username = ?, first_name = ?, last_name = ?, email = ?, phone_number = ?, home_address = ?, user_image = IFNULL(?, user_image) WHERE user_id = ?");
    $stmt->bind_param("sssssssi", $username, $first_name, $last_name, $email, $phone_number, $home_address, $image_path, $user_id);

    if ($stmt->execute()) {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=success" : "../Modules/customer/customer_profile.php?update=success";
        header("Location: $redirect_url");
    } else {
        $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php?update=error" : "../Modules/customer/customer_profile.php?update=error";
        header("Location: $redirect_url");
    }

    $stmt->close();
    $conn->close();
} else {
    $redirect_url = isVendor($user_id) ? "../Modules/vendor/vendor_profile.php" : "../Modules/customer/customer_profile.php";
    header("Location: $redirect_url");
    exit();
}