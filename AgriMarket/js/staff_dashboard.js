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
        for (let i = 0; i < 4; i++) {
            code += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return code;
    }

    document.getElementById("addPromotionModal").addEventListener("shown.bs.modal", function () {
        document.getElementById("discountCode").value = generateDiscountCode();
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const previewButtons = document.querySelectorAll(".btn-preview");

    previewButtons.forEach(button => {
        button.addEventListener("click", async () => {
            const productId = button.dataset.productId;
            const modalBody = document.getElementById("productPreviewBody");
            modalBody.innerHTML = `<div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

            const modal = new bootstrap.Modal(document.getElementById("productPreviewModal"));
            modal.show();

            try {
                const response = await fetch(`../../includes/get_product_details.php?product_id=${productId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const product = await response.json();

                if (product.error) {
                    throw new Error(product.error);
                }

                modalBody.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="me-4" style="max-width: 250px;">
                            <img src="../../${product.product_image}" alt="${product.product_name}" class="img-fluid" style="max-width: 100%; height: auto;">
                        </div>
                        <div>
                            <h4>${product.product_name}</h4>
                            <p><strong>Category:</strong> ${product.category_name}</p>
                            <p><strong>Price:</strong> RM ${parseFloat(product.unit_price).toFixed(2)}</p>
                            <p><strong>Available Stock:</strong> ${product.stock_quantity}</p>
                            <p><strong>Weight:</strong> ${product.weight} kg</p>
                            <p><strong>Description:</strong><br>${product.description || 'No description available.'}</p>
                        </div>
                    </div>
                `;
            } catch (error) {
                console.error("Preview error:", error);
                modalBody.innerHTML = `<p class="text-danger">Failed to load product details. ${error.message}</p>`;
            }
        });
    });
});

