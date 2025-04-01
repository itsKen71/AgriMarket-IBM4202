document.addEventListener("DOMContentLoaded", function () {
    // Check the data-edit-success attribute from the body tag
    const isSuccess = document.body.getAttribute("data-edit-success");

    if (isSuccess === "true") {
        // Initialize the success modal
        var successModal = new bootstrap.Modal(document.getElementById("editSuccessModal"));
        
        // Check if modal is initialized correctly
        if (successModal) {
            successModal.show();  // Show the modal
        }

        // Remove the query parameter from the URL after showing the modal
        const url = new URL(window.location);
        url.searchParams.delete("update");
        window.history.replaceState({}, document.title, url);
    }
});
