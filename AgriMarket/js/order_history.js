document.addEventListener('DOMContentLoaded', () => {
    // Quantity button handler
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('minus-btn') || e.target.classList.contains('plus-btn')) {
            const input = e.target.closest('.input-group')?.querySelector('input[type="number"]');
            if (!input) return;

            let current = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 9999;

            if (e.target.classList.contains('minus-btn') && current > min) {
                input.value = current - 1;
            } else if (e.target.classList.contains('plus-btn') && current < max) {
                input.value = current + 1;
            }
        }
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        const title = el.getAttribute('title') || el.getAttribute('data-bs-title');
        if (title) {
            new bootstrap.Tooltip(el);
        }
    });    
    
    // Preview
    document.querySelectorAll('.btn-preview').forEach(button => {
        button.addEventListener('click', () => showProductPreviewModal(button));
    });

    // Review
    document.querySelectorAll('.btn-review').forEach(button => {
        button.addEventListener('click', () => showReviewModal(button));
    });

    // Reorder (individual)
    document.querySelectorAll('.btn-reorder').forEach(button => {
        button.addEventListener('click', () => showReorderModal(button));
    });

    // Reorder All
    document.querySelectorAll('.btn-reorder-all').forEach(button => {
        button.addEventListener('click', () => {
        if (button.disabled) return;

            const products = JSON.parse(button.dataset.products);
            const reorderAllContent = document.getElementById('reorderAllContent');
            reorderAllContent.innerHTML = '';

            products.forEach(product => {
                if (product.status === 'Refunded') return;

                const row = document.createElement('div');
                row.className = 'd-flex align-items-center mb-3 border-bottom pb-2';

                row.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between gap-3 p-3 border rounded mb-3 product-item w-100" 
                        data-product-id="${product.product_id}" style="box-sizing: border-box;">
                        <div class="flex-shrink-0">
                            <img src="../../${product.product_image}" alt="${product.product_name}" style="height: 100px; width: 100px; object-fit: cover; border-radius: 0.5rem;">
                        </div>
                        <div class="flex-grow-1" style="min-width: 150px;">
                            <h6 class="mb-1">${product.product_name}</h6>
                            <small class="text-muted">Available: ${product.stock_quantity}</small>
                        </div>
                        <div style="min-width: 200px;">
                            <div class="input-group mb-1">
                                <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                                <input 
                                    type="number" 
                                    name="products[${product.product_id}][quantity]" 
                                    class="form-control quantity-input text-center" 
                                    value="1" 
                                    min="1" 
                                    max="${product.stock_quantity}" 
                                    ${product.stock_quantity <= 0 ? 'disabled' : ''}>
                                <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                            </div>
                            ${product.stock_quantity <= 0 ? '<small class="text-danger">Out of stock</small>' : ''}
                            <input type="hidden" name="products[${product.product_id}][product_id]" value="${product.product_id}">
                        </div>
                    </div>
                `;
                reorderAllContent.appendChild(row);
            });

            new bootstrap.Modal(document.getElementById('reorderAllModal')).show();
        });
    });

    // Submit Reorder All
    document.getElementById('reorderAllForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = {};
        formData.forEach((value, key) => {
            const match = key.match(/products\[(\d+)]\[(\w+)]/);
            if (match) {
                const productId = match[1];
                const field = match[2];
                data[productId] = data[productId] || {};
                data[productId][field] = value;
            }
        });

        const response = await fetch('../../includes/reorder_all.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });

        if (response.ok) {
            window.location.href = '?reorder=success';
        } else {
            alert('Reorder failed.');
        }
    });

    // Refund
    document.querySelectorAll('.btn-refund').forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productImage = button.dataset.productImage;
            const productQuantity = button.dataset.productQuantity;
            const productSubtotal = button.dataset.subPrice;
            const productPaymentId = button.dataset.paymentId;
            const orderId = button.dataset.orderId;

            document.getElementById('refundProductId').value = productId;
            document.getElementById('refundOrderId').value = orderId;
            document.getElementById('refundPaymentId').value = productPaymentId;
            document.getElementById('refundProductImage').src = productImage;
            document.getElementById('refundProductName').textContent = productName;
            document.getElementById('refundProductQuantity').textContent = productQuantity;
            document.getElementById('refundProductSubPrice').textContent = productSubtotal;
            document.getElementById('refundAmount').value = productSubtotal;
            document.getElementById('refundReason').value = '';

            const refundModal = new bootstrap.Modal(document.getElementById('refundModal'));
            refundModal.show();
        });
    });

    document.getElementById('refundForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const response = await fetch('../../includes/refund.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            window.location.href = '?refund=success';
        } else {
            alert('Refund request failed.');
        }
    });

    // Handle success modals and remove params
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('review') === 'success') {
        new bootstrap.Modal(document.getElementById('reviewSuccessModal')).show();
        urlParams.delete('review');
    }

    if (urlParams.get('reorder') === 'success') {
        new bootstrap.Modal(document.getElementById('reorderSuccessModal')).show();
        urlParams.delete('reorder');
    }

    if (urlParams.get('refund') === 'success') {
        new bootstrap.Modal(document.getElementById('refundSuccessModal')).show();
        urlParams.delete('refund');
    }

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

    new bootstrap.Modal(document.getElementById('productPreviewModal')).show();
}

// Review Modal
function showReviewModal(button) {
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;
    const productImage = button.dataset.productImage;

    document.getElementById('reviewProductId').value = productId;
    document.getElementById('reviewProductImage').src = productImage;
    document.getElementById('reviewProductName').textContent = productName;

    document.getElementById('star1').checked = true;
    document.getElementById('reviewText').value = '';

    fetch(`../../includes/get_review.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.rating) {
                document.getElementById(`star${data.rating}`).checked = true;
                document.getElementById('reviewText').value = data.review_description;
            }
        });

    new bootstrap.Modal(document.getElementById('reviewModal')).show();
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

    new bootstrap.Modal(document.getElementById('reorderModal')).show();
}
