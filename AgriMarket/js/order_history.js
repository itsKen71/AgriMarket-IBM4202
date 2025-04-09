document.addEventListener('DOMContentLoaded', () => {

    //Preview
    const previewButtons = document.querySelectorAll('.btn-preview');
    
    previewButtons.forEach(button => {
        button.addEventListener('click', function() {
            showProductPreviewModal(button);  // Show the preview modal with the clicked button's data
        });
    });
});

// Function to show the product preview modal with data
function showProductPreviewModal(button) {
    // Extract data from the button's data attributes
    const productId = button.getAttribute('data-product-id');
    const productName = button.getAttribute('data-product-name');
    const productImage = button.getAttribute('data-product-image');
    const productQuantity = button.getAttribute('data-product-quantity');
    const productPrice = button.getAttribute('data-product-price');
    const productDescription = button.getAttribute('data-product-description');
    const productWeight = button.getAttribute('data-product-weight');
    const productCategory = button.getAttribute('data-product-category');

    // Set the product details in the modal
    document.getElementById('productPreviewImage').src = productImage;  // Set product image
    document.getElementById('productPreviewName').textContent = productName;  // Set product name
    document.getElementById('productPreviewCategory').textContent = productCategory;  // You can set category if you have it
    document.getElementById('productPreviewDescription').textContent = productDescription;  // Set description
    document.getElementById('productPreviewStock').textContent = productQuantity;  // Set stock quantity
    document.getElementById('productPreviewWeight').textContent = productWeight;  // Set weight
    document.getElementById('productPreviewPrice').textContent = `RM ${parseFloat(productPrice).toFixed(2) || '0.00'}`;  // Set price with RM

    // Show the modal
    const previewModal = new bootstrap.Modal(document.getElementById('productPreviewModal'));
    previewModal.show();
}
