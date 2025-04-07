document.addEventListener("DOMContentLoaded", () => {
    const previewButtons = document.querySelectorAll(".btn-preview");

    previewButtons.forEach(button => { //Handle Preview Function
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
                    throw new Error(product.error);  // If error key exists in response
                }

                modalBody.innerHTML = `
                    <div class="d-flex align-items-center">
                        <!-- Product Image -->
                        <div class="me-4" style="max-width: 250px;">
                            <img src="../../${product.product_image}" alt="${product.product_name}" class="img-fluid" style="max-width: 100%; height: auto;">
                        </div>

                        <!-- Product Details -->
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

    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    const reviewStars = document.getElementById('reviewStars');
    const ratingValue = document.getElementById('ratingValue');

   // Open modal when review button is clicked
document.querySelectorAll('.btn-review').forEach(button => {
    button.addEventListener('click', async () => {
        const productId = button.dataset.productId;
        const productName = button.dataset.productName;
        const productImage = button.dataset.productImage;

        document.getElementById('reviewProductId').value = productId;
        document.getElementById('reviewProductName').innerText = productName;
        document.getElementById('reviewProductImage').src = productImage;

        // Default values
        let currentRating = 1;
        let currentDescription = '';

        // Fetch existing review if any
        try {
            const response = await fetch(`../../includes/get_review.php?product_id=${productId}`);
            if (response.ok) {
                const data = await response.json();
                if (data && data.rating) {
                    currentRating = data.rating;
                    currentDescription = data.review_description;
                }
            }
        } catch (err) {
            console.warn("No existing review found.");
        }

        // Set stars and description
        generateStars(currentRating);
        document.getElementById('ratingValue').value = currentRating;
        document.getElementById('reviewDescription').value = currentDescription;

        reviewModal.show();
    });
});


function generateStars(defaultRating = 1) {
    reviewStars.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('i');
        star.className = 'bi bi-star-fill mx-1';
        star.dataset.value = i;
        star.style.cursor = 'pointer';
        star.classList.add(i <= defaultRating ? 'text-warning' : 'text-secondary');

        star.addEventListener('click', () => {
            ratingValue.value = i;
            document.querySelectorAll('#reviewStars i').forEach((el, index) => {
                el.classList.toggle('text-warning', index < i);
                el.classList.toggle('text-secondary', index >= i);
            });
        });

        // Optional: Add hover animation
        star.addEventListener('mouseenter', () => {
            document.querySelectorAll('#reviewStars i').forEach((el, index) => {
                el.classList.toggle('text-warning', index < i);
                el.classList.toggle('text-secondary', index >= i);
            });
        });
        star.addEventListener('mouseleave', () => {
            const selectedRating = parseInt(ratingValue.value);
            document.querySelectorAll('#reviewStars i').forEach((el, index) => {
                el.classList.toggle('text-warning', index < selectedRating);
                el.classList.toggle('text-secondary', index >= selectedRating);
            });
        });

        reviewStars.appendChild(star);
    }
}

    // Submit review via AJAX
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../../includes/submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            reviewModal.hide();
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        })
        .catch(err => {
            console.error(err);
            alert('There was an error submitting your review.');
        });
    });
    
});
