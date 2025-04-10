document.addEventListener('DOMContentLoaded', () => {
    // Preview
    document.querySelectorAll('.btn-preview').forEach(button => {
        button.addEventListener('click', () => showProductPreviewModal(button));
    });

    // Review
    document.querySelectorAll('.btn-review').forEach(button => {
        button.addEventListener('click', () => showReviewModal(button));
    });

    // Reorder
    document.querySelectorAll('.btn-reorder').forEach(button => {
        button.addEventListener('click', () => showReorderModal(button));
    });

    // Handle success modals and remove params
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('review') === 'success') {
        const modal = new bootstrap.Modal(document.getElementById('reviewSuccessModal'));
        modal.show();
        urlParams.delete('review');
    }

    if (urlParams.get('reorder') === 'success') {
        const modal = new bootstrap.Modal(document.getElementById('reorderSuccessModal'));
        modal.show();
        urlParams.delete('reorder');
    }

    // Update URL after removing params
    const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
    window.history.replaceState({}, '', newUrl.endsWith('?') ? newUrl.slice(0, -1) : newUrl);
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

    // Fetch existing review
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

// Reorder Modal
function showReorderModal(button) {
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;
    const productImage = button.dataset.productImage;
    const productStock = button.dataset.productStock;

    document.getElementById('reorderProductId').value = productId;
    document.getElementById('reorderProductImage').src = productImage;
    document.getElementById('reorderProductName').textContent = productName;
    document.getElementById('reorderStock').textContent = productStock;
    document.getElementById('reorderQuantity').value = 1;
    document.getElementById('reorderQuantity').max = productStock;

    const reorderModal = new bootstrap.Modal(document.getElementById('reorderModal'));
    reorderModal.show();
}


