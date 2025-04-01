document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".edit-btn");

    // Display original record
    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("editProductId").value = this.dataset.id;
            document.getElementById("editProductName").value = this.dataset.name; // Show name but can't be edited
            document.getElementById("editDescription").value = this.dataset.description;
            document.getElementById("editStock").value = this.dataset.stock;
            document.getElementById("editWeight").value = this.dataset.weight;
            document.getElementById("editPrice").value = this.dataset.price;

            const selectedCategoryName = this.dataset.category;
            const editCategoryDropdown = document.getElementById("editCategory");

            for (let i = 0; i < editCategoryDropdown.options.length; i++) {
                if (editCategoryDropdown.options[i].text.trim() === selectedCategoryName.trim()) {
                    editCategoryDropdown.selectedIndex = i;
                    break;
                }
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Get the success message flags from body attributes
    const isAddSuccess = document.body.getAttribute("data-success");
    const isEditSuccess = document.body.getAttribute("data-edit-success");

    // Function to show modal and clean URL
    function showModal(modalId, param) {
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        if (modal) {
            modal.show(); // Show the modal
        }

        // Remove the parameter from the URL after showing modal
        const url = new URL(window.location);
        url.searchParams.delete(param);
        window.history.replaceState({}, document.title, url);
    }

    if (isAddSuccess === "true") {
        showModal("successModal", "add");
    }

    if (isEditSuccess === "true") {
        showModal("editSuccessModal", "edit");
    }
});

