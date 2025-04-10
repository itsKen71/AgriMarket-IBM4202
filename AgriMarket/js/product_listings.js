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
            document.getElementById("deleteProductId").value = this.dataset.id;
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
    const isDeleteSuccess = document.body.getAttribute("data-delete-success");

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
    if (isDeleteSuccess === "true") {
        showModal("deleteSuccessModal", "delete");
    }

    // Notification Toggle Logic
    const notificationToggle = document.getElementById("notificationToggle");

    // Retrieve the notification preference from localStorage
    const isNotificationsOn = localStorage.getItem("notifications") === "true";
    notificationToggle.checked = isNotificationsOn;

    notificationToggle.addEventListener("change", function () {
        localStorage.setItem("notifications", this.checked);
        if (!this.checked) {
            // Clear any existing toasts if notifications are turned off
            const toastContainer = document.getElementById("toastContainer");
            if (toastContainer) {
                toastContainer.innerHTML = ""; // Remove all toasts
            }
        }
    });

    // Exit if notifications are disabled
    if (!isNotificationsOn) return;

    // Check if the lowStockProducts array is available and has data
    if (window.lowStockProducts && window.lowStockProducts.length > 0) {
        let delay = 0;

        // Loop through each product and display a toast
        window.lowStockProducts.forEach((product, index) => {
            setTimeout(() => {
                showLowStockToast(product); // Pass the correct product to the function
            }, delay);
            delay += 1500; // Add delay for each subsequent toast
        });
    }

    // Fetch reminders and show toasts
    const now = new Date();

    let delay = 0;

    tasks.forEach((task, index) => {
        const dueDate = new Date(task.dueDate);
        const reminderTime = task.reminderTime;
        const reminderStart = new Date(dueDate.getTime() - reminderTime * 60 * 60000);
        const reminderEnd = new Date(reminderStart.getTime() + 30 * 60000);

        // Check if the current time is within the reminder window
        if (now >= reminderStart && now <= reminderEnd) {
            setTimeout(() => {
                showToast(task.title, reminderTime);
            }, delay);
            delay += 1000;
        }
    });
});

// Function to show toast for low stock products
function showLowStockToast(product) {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;

    // Play sound for the low stock alert
    const sound = new Audio("../../Assets/audio/alert.mp3");
    sound.play();

    // Create a unique ID for the toast
    const toastID = `toast-${Date.now()}`;

    // Create the toast HTML
    const toastHTML = `
    <div id="${toastID}" class="toast fade align-items-center text-dark bg-light border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="toast-header">
            <img src="../../Assets/svg/bell.svg" class="rounded me-2" alt="Notification Bell" width="20">
            <strong class="me-auto">Low Stock Alert</strong>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <strong>${product.product_name}</strong>: Only ${product.stock_quantity} left.
        </div>
    </div>
    `;

    // Append the toast to the container
    toastContainer.insertAdjacentHTML("beforeend", toastHTML);

    // Get the newly created toast element
    const toastElement = document.getElementById(toastID);
    const bootstrapToast = new bootstrap.Toast(toastElement);

    // Show the toast
    bootstrapToast.show();

    // Add an event listener to remove the toast after it fades out
    toastElement.addEventListener("hidden.bs.toast", () => {
        toastElement.remove();
    });
}
