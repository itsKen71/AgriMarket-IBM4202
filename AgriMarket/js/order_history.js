document.addEventListener('DOMContentLoaded', () => {
    // Preview
    document.querySelectorAll('.btn-preview').forEach(button => {
        button.addEventListener('click', () => showProductPreviewModal(button));
    });

    // Review
    document.querySelectorAll('.btn-review').forEach(button => {
        button.addEventListener('click', () => showReviewModal(button));
    });

    // Show success modal if review was submitted
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('review') === 'success') {
        const successModal = new bootstrap.Modal(document.getElementById('reviewSuccessModal'));
        successModal.show();
        urlParams.delete('review');
        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        window.history.replaceState({}, '', newUrl);
    }
});

// Preview Modal
function showProductPreviewModal(button) {
    document.getElementById('productPreviewImage').src = button.dataset.productImage;
    document.getElementById('productPreviewName').textContent = button.dataset.productName;
    document.getElementById('productPreviewCategory').textContent = button.dataset.productCategory;
    document.getElementById('productPreviewDescription').textContent = button.dataset.productDescription;
    document.getElementById('productPreviewStock').textContent = button.dataset.productQuantity;
    document.getElementById('productPreviewWeight').textContent = `${button.dataset.productWeight} kg`;
    document.getElementById('productPreviewPrice').textContent = `RM ${parseFloat(button.dataset.productPrice).toFixed(2)}`;

    const previewModal = new bootstrap.Modal(document.getElementById('productPreviewModal'));
    previewModal.show();
}

// Review Modal
function showReviewModal(button) {
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;
    const productImage = button.dataset.productImage;

    document.getElementById('reviewProductId').value = productId;
    document.getElementById('reviewProductImage').src = productImage;
    document.getElementById('reviewProductName').textContent = productName;

    // Reset to default
    document.getElementById('star1').checked = true;
    document.getElementById('reviewText').value = '';

    // Fetch existing review if any
    fetch(`../../includes/get_review.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.rating) {
                document.getElementById(`star${data.rating}`).checked = true;
                document.getElementById('reviewText').value = data.review_description;
            }
        });

    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    reviewModal.show();
}
