<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $home_address = trim($_POST['home_address']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $profile_image = $_FILES['profile_image'];

    if ($password !== $confirm_password) {
        header("Location: ../Modules/admin/admin_dashboard.php?error=password_mismatch");
        exit();
    }

    $hashed_password = hash('sha256', $password);

    // Handle profile image upload
    $upload_dir = realpath(__DIR__ . "/../../AgriMarket/Assets/img/profile_img/") . "/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $image_path = "Assets/img/profile_img/default.png"; // Default image
    if (!empty($profile_image['name'])) {
        $image_name = basename($profile_image['name']);
        $target_file = $upload_dir . $image_name;
        $image_path = "Assets/img/profile_img/" . $image_name;

        if (!move_uploaded_file($profile_image['tmp_name'], $target_file)) {
            header("Location: ../Modules/admin/admin_dashboard.php?error=image_upload_failed");
            exit();
        }
    }

    $result = insertUser($first_name, $last_name, $username, $email, $hashed_password, $role, $phone_number, $home_address, $image_path);

    if ($result) {
        header("Location: ../Modules/admin/admin_dashboard.php?success=staff_added");
    } else {
        header("Location: ../Modules/admin/admin_dashboard.php?error=duplicate_entry");
    }
    exit();
}
?>