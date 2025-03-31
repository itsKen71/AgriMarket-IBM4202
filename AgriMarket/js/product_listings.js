document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".edit-btn");

    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("editProductId").value = this.dataset.id;
            document.getElementById("editProductName").value = this.dataset.name;
            document.getElementById("editDescription").value = this.dataset.description;
            document.getElementById("editStock").value = this.dataset.stock;
            document.getElementById("editWeight").value = this.dataset.weight;
            document.getElementById("editPrice").value = this.dataset.price;
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Get the success message flag from body attribute
    const isSuccess = document.body.getAttribute("data-success");

    if (isSuccess === "true") {
        // Initialize the success modal
        var successModal = new bootstrap.Modal(document.getElementById("successModal"));

        // Check if modal is initialized correctly
        if (successModal) {
            successModal.show();  // Show the modal
        }

        // Remove the ?add=success from the URL after showing modal
        const url = new URL(window.location);
        url.searchParams.delete("add");
        window.history.replaceState({}, document.title, url);
    }
});

