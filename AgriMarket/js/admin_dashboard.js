function editVendorListing(element) {
    let vendorId = element.getAttribute("data-vendor-id");
    let storeName = element.getAttribute("data-store");
    let subscriptionType = element.getAttribute("data-subscription-type");
    let expirationDate = element.getAttribute("data-expiration-date");
    let staffAssigned = element.getAttribute("data-staff-assistance");
    let staffAssignedID = element.getAttribute("data-staff-assistance-id");

    document.getElementById("show-store").textContent = storeName;
    document.getElementById("show-subscription").textContent = subscriptionType;
    document.getElementById("show-expiration").textContent = expirationDate;
    document.getElementById("show-staff").textContent = staffAssigned;
    
    // Store vendor ID & staff ID for reference
    document.getElementById("show-store").setAttribute("data-vendor-id", vendorId);
    document.getElementById("show-store").setAttribute("data-staff-assistance-id", staffAssignedID);

    let staffSelect = document.getElementById("staff-select");
    let staffContainer = document.getElementById("staff-selection-container");

    // Show dropdown only for Tier 3
    if (subscriptionType === "Tier 3") {
        staffContainer.style.display = "block";
        staffSelect.disabled = false;
    } else {
        staffContainer.style.display = "none";
        staffSelect.disabled = true;
    }

    // Pre-select assigned staff
    staffSelect.value = staffAssignedID ? staffAssignedID : staffSelect.options[0].value;

    // Open Bootstrap Modal
    new bootstrap.Modal(document.getElementById("editVendorModal")).show();
}

function updateVendor() {
    let vendorId = document.getElementById("show-store").getAttribute("data-vendor-id");
    let selectedStaff = document.getElementById("staff-select").value;
    let currentStaffID = document.getElementById("show-store").getAttribute("data-staff-assistance-id");

    // Normalize values for comparison
    let selectedStaffNormalized = selectedStaff ? selectedStaff.trim() : "";
    let currentStaffNormalized = currentStaffID ? currentStaffID.trim() : "";

    // If no change is detected, close the modal and exit function
    if (selectedStaffNormalized === currentStaffNormalized) {
        bootstrap.Modal.getInstance(document.getElementById("editVendorModal")).hide();
        return;
    }

    fetch('../../includes/assign_staff.php', {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        credentials: 'include',
        body: `vendor_id=${encodeURIComponent(vendorId)}&staff_id=${encodeURIComponent(selectedStaff)}`
    });
}
