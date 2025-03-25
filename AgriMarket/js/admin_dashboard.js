document.addEventListener("DOMContentLoaded", function () {

    var toggleVendorBtn = document.getElementById("toggleVendor");
    var vendorList = document.getElementById("vendorList");

    if (toggleVendorBtn && vendorList) {
        toggleVendorBtn.addEventListener("click", function () {
            vendorList.style.display = (vendorList.style.display === "none") ? "block" : "none";
        });
    }


    var toggleStaffBtn = document.getElementById("toggleStaff");
    var staffListing = document.getElementById("staffListing");

    if (toggleStaffBtn && staffListing) {
        toggleStaffBtn.addEventListener("click", function () {
            staffListing.style.display = (staffListing.style.display === "none") ? "block" : "none";
        });
    }
});
