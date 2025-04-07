document.addEventListener("DOMContentLoaded", function () {
    // Edit Button Logic
    const editButtons = document.querySelectorAll(".edit-btn");
    const imagePreview = document.getElementById("imagePreview");
    const editImageInput = document.getElementById("editProductImage");
    const currentImageNameInput = document.getElementById("currentImageName");
    const currentImageInput = document.getElementById("currentImage");

    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            // Populate form fields with existing data
            document.getElementById("editProductId").value = this.dataset.id;
            document.getElementById("editProductName").value = this.dataset.name;
            document.getElementById("editDescription").value = this.dataset.description;
            document.getElementById("editStock").value = this.dataset.stock;
            document.getElementById("editWeight").value = this.dataset.weight;
            document.getElementById("editPrice").value = this.dataset.price;
            document.getElementById("editProductStatus").value = this.dataset.status;  

            // Reset file input and image name field
            editImageInput.value = "";
            currentImageNameInput.value = "";

            // Set category dropdown selection
            const selectedCategoryName = this.dataset.category;
            const editCategoryDropdown = document.getElementById("editCategory");
            for (let i = 0; i < editCategoryDropdown.options.length; i++) {
                if (editCategoryDropdown.options[i].text.trim() === selectedCategoryName.trim()) {
                    editCategoryDropdown.selectedIndex = i;
                    break;
                }
            }

            // Get current image path from data attribute
            const currentImagePath = this.dataset.image;

            if (currentImagePath) {
                const currentFileName = currentImagePath.split('/').pop();
                currentImageNameInput.value = currentFileName;
                currentImageInput.value = currentImagePath;
                imagePreview.src = `../../${currentImagePath}`;
                imagePreview.style.display = "block";
            } else {
                imagePreview.style.display = "none";
            }
        });
    });

    // When a new file is selected, update the preview 
    editImageInput.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = "block";
            };
            reader.readAsDataURL(file);
            currentImageNameInput.value = file.name;  // Update selected file name in hidden input
        }
    });

    // Handle success messages
    const isAddSuccess = document.body.getAttribute("data-success");
    const isEditSuccess = document.body.getAttribute("data-edit-success");

    function showModal(modalId, param) {
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        if (modal) {
            modal.show();
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

    // Check if the lowStockProducts array is available and has data
    if (window.lowStockProducts && window.lowStockProducts.length > 0) {
        // Loop through each product and display a toast
        window.lowStockProducts.forEach((product, index) => {
            showLowStockToast(product, index);
        });
    }
});

// Function to show toast for low stock products
function showLowStockToast(product, index) {
    // Create a toast element dynamically
    const toastContainer = document.getElementById('toastContainer');

    const toast = document.createElement('div');
    toast.classList.add('toast', 'align-items-center', 'text-dark', 'bg-light', 'border-0', 'fade');
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-bs-autohide', 'false');
    toast.style.maxWidth = '300px';
    toast.style.width = '300px';

    // Toast content
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">Low Stock Alert</strong>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <strong>${product.product_name}</strong>: Only ${product.stock_quantity} left.
        </div>
    `;

    // Append the toast to the container
    toastContainer.appendChild(toast);

    // Show the toast
    const toastInstance = new bootstrap.Toast(toast);
    toastInstance.show();

    // Set a timeout to remove the toast after a delay
    const delay = (toastContainer.children.length) * 1200; // Delay increases for each subsequent toast
    setTimeout(() => {
        const newestToast = toastContainer.lastElementChild;
        if (newestToast) {
            newestToast.classList.remove('show');
            setTimeout(() => {
                newestToast.remove();
            }, 50); 
        }
    }, delay);
}
