<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../../Modules/authentication/login.php");
    exit(); 
}

include '../includes/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendorId = $_POST['vendor_id'] ?? '';
    $staffId = $_POST['staff_id'] ?? '';

    // Call Function to Update Vendor's Assistance
    $updateSuccess = updateVendorAssistance($vendorId, $staffId);    

    if ($updateSuccess) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Vendor assistance updated successfully',
            'user_id' => $staffId,
            'vendor_id' => $vendorId
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update vendor assistance'
        ]);
    }
    
}
?>