<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = intval($_POST['review_id']);
    $user_role = $_SESSION['role'] ?? '';

    // Ensure only staff can delete comments
    if ($user_role === 'Staff') {
        if (deleteComment($review_id)) {
            $_SESSION['message'] = "Comment deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete the comment.";
        }
    } else {
        $_SESSION['error'] = "Unauthorized action.";
    }
}

header("Location: ../Modules/customer/product_page.php");
exit();