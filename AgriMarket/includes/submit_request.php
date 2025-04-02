<?php
session_start();
include 'database.php';

$vendor_id = 1; // Temporary for testing
//$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$vendor_id) {
    header("Location: ../../Modules/authentication/login.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'] ?? null;
    $request_type = $_POST['request_type'] ?? null;
    $request_description = $_POST['request_description'] ?? null;
    
    if ($vendor_id && $request_type) {
        $query = "INSERT INTO request (vendor_id, request_type, request_description, request_date) 
                  VALUES (?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $vendor_id, $request_type, $request_description);
        
        if ($stmt->execute()) {
            header("Location: ../Modules/vendor/vendor_profile.php?request=success"); 
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
