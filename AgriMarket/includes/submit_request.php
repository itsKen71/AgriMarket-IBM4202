<?php
session_start();
include 'database.php';

$user_id = 2; // Temporary for testing
//$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'] ?? null;
    $request_type = $_POST['request_type'] ?? null;
    $request_description = $_POST['request_description'] ?? null;
    
    if ($vendor_id && $request_type) {
        $isInserted = insertRequest($conn, $vendor_id, $request_type, $request_description);
        
        if ($isInserted) {
            header("Location: ../Modules/vendor/vendor_profile.php?request=success"); 
            exit();
        } else {
            echo "Error: Failed to submit the request.";
        }
    }
}
?>
