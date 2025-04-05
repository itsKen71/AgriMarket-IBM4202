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

    if ($password !== $confirm_password) {
        header("Location: ../Modules/admin/admin_dashboard.php?error=password_mismatch");
        exit();
    }

    $hashed_password = hash('sha256', $password);

    $result = insertUser($first_name, $last_name, $username, $email, $hashed_password, $role, $phone_number, $home_address);

    if ($result) {
        header("Location: ../Modules/admin/admin_dashboard.php?success=staff_added");
    } else {
        header("Location: ../Modules/admin/admin_dashboard.php?error=duplicate_entry");
    }
    exit();
}
?>