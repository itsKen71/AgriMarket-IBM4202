function editVendorListing(button) {
    let vendorId = button.getAttribute("data-vendor-id");
    let storeName = button.getAttribute("data-store");
    let subscriptionType = button.getAttribute("data-subscription-type");
    let expirationDate = button.getAttribute("data-expiration-date");
    let currentAssistance = button.getAttribute("data-assistance-name");

    document.getElementById("show-store").textContent = storeName;
    document.getElementById("show-subscription").textContent = subscriptionType;
    document.getElementById("show-expiration").textContent = expirationDate;
    document.getElementById("show-staff").textContent = currentAssistance;

    // Set the vendor ID in the select dropdown
    let staffSelect = document.getElementById("staff-select");
    staffSelect.setAttribute("data-vendor-id", vendorId);

    // Show the modal
    var modal = new bootstrap.Modal(document.getElementById('editVendorModal'));
    modal.show();
}

function updateVendor() {

    let vendorId = document.getElementById("staff-select").getAttribute("data-vendor-id");
    let selectedStaffId = document.getElementById("staff-select").value;
    let selectedStaffName = document.querySelector(`#staff-select option[value="${selectedStaffId}"]`).getAttribute("data-name");

    console.log("Updating Vendor ID:", vendorId);

    fetch("../../Modules/admin/admin_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            vendor_id: vendorId,
            staff_id: selectedStaffId
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data);

            if (data.status === "success") {
                // Update the UI with the new staff assistance
                let vendorCard = document.querySelector(`[data-vendor-id="${vendorId}"]`);
                if (vendorCard) {
                    let staffSpan = vendorCard.querySelector(".staff-assistance");
                    if (staffSpan) {
                        staffSpan.textContent = selectedStaffName;
                    }
                }

                // Hide modal after update
                let modal = bootstrap.Modal.getInstance(document.getElementById("editVendorModal"));
                modal.hide();
            } else {
                alert("Failed to update vendor assistance.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while updating.");
        });
}
