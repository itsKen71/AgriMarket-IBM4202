//Pending Details Modal
function showPendingDetails(button) {
    document.getElementById("show-store").textContent = button.dataset.store;
    document.getElementById("show-product").textContent = button.dataset.product;
    document.getElementById("show-category").textContent = button.dataset.category;
    document.getElementById("show-description").textContent = button.dataset.description;
    document.getElementById("show-stock").textContent = button.dataset.stock;
    document.getElementById("show-weight").textContent = button.dataset.weight;
    document.getElementById("show-price").textContent = button.dataset.price;

    var myModal = new bootstrap.Modal(document.getElementById('pendingRequestModal'));
    myModal.show();

}

//Auto-Generate Discount Code(Promotion)
document.addEventListener("DOMContentLoaded", function () {
    function generateDiscountCode() {
        const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        let code = "PROMO-";
        for (let i = 0; i < 6; i++) {
            code += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return code;
    }

    document.getElementById("addPromotionModal").addEventListener("shown.bs.modal", function () {
        document.getElementById("discountCode").value = generateDiscountCode();
    });
});

//Limit start date and end date 
document.addEventListener("DOMContentLoaded", function () {
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");

    // Set the minimum start date to today
    startDateInput.setAttribute("min", new Date().toISOString().split("T")[0]);

    // Handle Start Date and End Date 
    startDateInput.addEventListener("input", function () {
        const startDateValue = startDateInput.value;
        if (startDateValue) {
            // Calculate the date one day after the start date
            const startDate = new Date(startDateValue);
            startDate.setDate(startDate.getDate() + 1);

            const endDateMin = startDate.toISOString().split("T")[0];

            endDateInput.setAttribute("min", endDateMin);
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('promotionForm');
    const submitBtn = document.getElementById('submit-button');
    const spinner = document.getElementById('submit-spinner');
    const buttonText = document.getElementById('submit-text');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const requiredFields = form.querySelectorAll('[required]');
        const emptyField = Array.from(requiredFields).find(field => !field.value.trim());
        if (emptyField) {
            alert("Please fill out all required fields.");
            return;
        }

        // Show spinner
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.textContent = 'Updating...';

        const formData = new FormData(form);

        fetch("../../Modules/staff/staff_dashboard.php", {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(response => {
                console.log(response);
                const modalEl = document.getElementById('addPromotionModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();
            })
            .catch((error) => {
                console.error('Error:', error);
                alert("Something went wrong. Please try again.");
            })
            .finally(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                buttonText.textContent = 'Update';
            });
    });
});

//Update pending status
function updatePendingStatus(productId, action) {
    fetch("../../Modules/staff/staff_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            ajax: '1',
            action: action,
            product_id: productId
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data);
            if (data.status === "success") {
                const card = document.querySelector(`.Pending-Card[data-product-id="${productId}"]`);
                if (card) {
                    card.remove();
                }
            } else {
                alert("Failed to update product status.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}

//update refund status
function updateRefundStatus(refundId, status) {
    fetch("../../Modules/staff/staff_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            ajax: '1',
            action: "refund",
            refund_id: refundId,
            status: status
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data);
            if (data.status === "success") {
                const card = document.querySelector(`.Refund-Card[data-refund-id="${refundId}"]`);
                if (card) {
                    card.remove();
                }
            } else {
                alert("Failed to update refund status.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}

//update assistance status
function markAssistanceComplete(requestId) {
    fetch("../../Modules/staff/staff_dashboard.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            ajax: '1',
            action: "assistance",
            request_id: requestId
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data);
            if (data.status === "success") {
                const card = document.querySelector(`.Assisstance-Card[data-request-id="${requestId}"]`);
                if (card) {
                    card.remove();
                }
            } else {
                alert("Failed to mark assistance as complete.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred.");
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const previewButtons = document.querySelectorAll(".btn-preview");

    previewButtons.forEach(button => {
        button.addEventListener("click", () => {
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productImage = button.dataset.productImage;
            const productCategory = button.dataset.productCategory;
            const productDescription = button.dataset.productDescription;
            const productStock = button.dataset.productStock;
            const productWeight = button.dataset.productWeight;
            const productPrice = button.dataset.productPrice;

            const modalBody = document.getElementById("productPreviewBody");

            // Populate the modal with product details
            modalBody.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="me-4" style="max-width: 250px;">
                        <img src="../../${productImage}" alt="${productName}" class="img-fluid" style="max-width: 100%; height: auto;">
                    </div>
                    <div>
                        <h4>${productName}</h4>
                        <p><strong>Category:</strong> ${productCategory}</p>
                        <p><strong>Price:</strong> RM ${parseFloat(productPrice).toFixed(2)}</p>
                        <p><strong>Available Stock:</strong> ${productStock}</p>
                        <p><strong>Weight:</strong> ${productWeight} kg</p>
                        <p><strong>Description:</strong><br>${productDescription || 'No description available.'}</p>
                    </div>
                </div>
            `;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById("productPreviewModal"));
            modal.show();
        });
    });
});