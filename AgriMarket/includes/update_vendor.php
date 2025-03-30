<?php
session_start();
include '../includes/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendorId = $_POST['vendor_id'] ?? '';
    $staffId = $_POST['staff_id'] ?? '';

    // Call Function to Update Vendor's Assistance
    updateVendorAssistance($vendorId, $staffId);    
    
}
?>