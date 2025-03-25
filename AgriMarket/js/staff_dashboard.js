document.addEventListener("DOMContentLoaded", function () {
    // Toggle Pending Requests Section
    var togglePendingBtn = document.getElementById("togglePending");
    var pendingDiv = document.getElementById("pendingRequests");

    if (togglePendingBtn && pendingDiv) {
        togglePendingBtn.addEventListener("click", function () {
            pendingDiv.style.display = (pendingDiv.style.display === "none") ? "block" : "none";
        });
    }

    // Toggle Assistance Requests Section
    var toggleAssistanceBtn = document.getElementById("toggleAssistance");
    var assistanceDiv = document.getElementById("assistanceRequests");

    if (toggleAssistanceBtn && assistanceDiv) {
        toggleAssistanceBtn.addEventListener("click", function () {
            assistanceDiv.style.display = (assistanceDiv.style.display === "none") ? "block" : "none";
        });
    }

    // Attach event listener to all "View Details" buttons
    document.querySelectorAll(".view_detail_pending_requestBTN").forEach(button => {
        button.addEventListener("click", function () {
            showPendingDetails(this);
        });
    });

    // Close modal event listener
    document.querySelector(".close").addEventListener("click", function () {
        document.querySelector(".Details_Container").classList.remove("active");
    });
});


// Function to show pending request details
function showPendingDetails(element) {

    document.getElementById("show-store").textContent = element.getAttribute("get-data-store");
    document.getElementById("show-product").textContent = element.getAttribute("get-data-product");
    document.getElementById("show-category").textContent = element.getAttribute("get-data-category");
    document.getElementById("show-description").textContent = element.getAttribute("get-data-description");
    document.getElementById("show-stock").textContent = element.getAttribute("get-data-stock");
    document.getElementById("show-weight").textContent = element.getAttribute("get-data-weight");
    document.getElementById("show-price").textContent = element.getAttribute("get-data-price");


    document.querySelector(".Details_Container").classList.add("active");
}


function closePendingDetails() {
    document.querySelector(".Details_Container").classList.remove("active");
}
