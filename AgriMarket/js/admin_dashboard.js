function editVendorListing(button) {
    let vendorId = button.getAttribute("data-vendor-id");
    let storeName = button.getAttribute("data-store");
    let subscriptionType = button.getAttribute("data-subscription-type");
    let expirationDate = button.getAttribute("data-expiration-date");
    let currentAssistance = button.getAttribute("data-assistance-name");
    let currentAssistanceId = button.getAttribute("data-assistance-id");

    document.getElementById("show-store").textContent = storeName;
    document.getElementById("show-subscription").textContent = subscriptionType;
    document.getElementById("show-expiration").textContent = expirationDate;
    document.getElementById("show-staff").textContent = currentAssistance || "None";

    document.getElementById("staff-select").value = currentAssistanceId;

    // Store vendor ID in modal 
    document.getElementById("staff-select").setAttribute("data-vendor-id", vendorId);

    let modal = new bootstrap.Modal(document.getElementById("editVendorModal"));
    modal.show();
}

function updateVendor() {
    let vendorId = document.getElementById("staff-select").getAttribute("data-vendor-id");
    let selectedStaffId = document.getElementById("staff-select").value;
    let selectedStaffName = document.querySelector(`#staff-select option[value="${selectedStaffId}"]`).textContent;

    console.log("Updating Vendor ID:", vendorId);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../../includes/update_vendor.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log("Server Response:", xhr.responseText); 

            // Update UI  efore modal closing 
            let vendorCard = document.querySelector(`[data-vendor-id="${vendorId}"]`);
            if (vendorCard) {
                let staffSpan = vendorCard.querySelector(".staff-assistance");
                if (staffSpan) {
                    staffSpan.textContent = selectedStaffName;
                }
            }

            let modal = bootstrap.Modal.getInstance(document.getElementById("editVendorModal"));
            modal.hide();
        }
    };

    xhr.send("vendor_id=" + encodeURIComponent(vendorId) + "&staff_id=" + encodeURIComponent(selectedStaffId));
}
