
    // add to cart form validate
    document.addEventListener('DOMContentLoaded', function() {
        const quantityModal = document.getElementById('quantityModal');
        const cartForm = document.getElementById('cartForm');
        const quantityInput = document.getElementById('quantityInput');
        const submitButton = cartForm.querySelector('button[type="submit"]');
        let stockQuantity = 0;

        // when modal opens, get the stock quantity from the button
        quantityModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            stockQuantity = parseInt(button.getAttribute('data-stock-quantity'));
            document.getElementById('availableStock').textContent = stockQuantity;
            
            // reset form state when opening
            quantityInput.classList.remove('is-invalid');
            quantityInput.value = 1;
            submitButton.disabled = false;
        });

        // real-time validate quantity
        quantityInput.addEventListener('input', function() {
            validateQuantity();
        });

        // form submission handler
        cartForm.addEventListener('submit', function(e) {
            if (!validateQuantity()) {
                e.preventDefault();
            }
        });

        function validateQuantity() {
            const quantity = parseInt(quantityInput.value) || 0;
            let isValid = true;
            
            // clear previous invalid states
            quantityInput.classList.remove('is-invalid');
            
            // check if quantity is at least 1 and a number
            if (quantity < 1 || isNaN(quantity)) {
                quantityInput.classList.add('is-invalid');
                quantityInput.nextElementSibling.textContent = 'The quantity must be at least 1';
                isValid = false;
            } 
            // check if quantity exceeds stock
            else if (quantity > stockQuantity) {
                quantityInput.classList.add('is-invalid');
                quantityInput.nextElementSibling.textContent = `Only ${stockQuantity} items available in stock`;
                isValid = false;
            }
            
            // disable submit button if invalid
            submitButton.disabled = !isValid;
            
            return isValid;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
    const buyNowModal = document.getElementById('buyNowModal');
    const buyNowForm = document.getElementById('buyNowForm');
    const quantityInput = document.getElementById('buyNowQuantity');
    const submitButton = buyNowForm.querySelector('button[type="submit"]');
    let stockQuantity = 0;

    // When modal opens, get the stock quantity from the button
    buyNowModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        stockQuantity = parseInt(button.getAttribute('data-stock-quantity'));
        
        // Reset form state when opening
        quantityInput.classList.remove('is-invalid');
        quantityInput.value = 1;
        submitButton.disabled = false;
    });

    // Validate quantity on input change
    quantityInput.addEventListener('input', function() {
        validateQuantity();
    });

    // Form submission handler
    buyNowForm.addEventListener('submit', function(e) {
        if (!validateQuantity()) {
            e.preventDefault();
        }
    });

    function validateQuantity() {
        const quantity = parseInt(quantityInput.value);
        let isValid = true;
        
        // Clear previous invalid states
        quantityInput.classList.remove('is-invalid');
        
        // Check if quantity is at least 1 and a number
        if (isNaN(quantity) || quantity < 1) {
            quantityInput.classList.add('is-invalid');
            quantityInput.nextElementSibling.textContent = 'The quantity must be at least 1';
            isValid = false;
        } 
        // Check if quantity exceeds stock
        else if (quantity > stockQuantity) {
            quantityInput.classList.add('is-invalid');
            quantityInput.nextElementSibling.textContent = `Only ${stockQuantity} items available in stock`;
            isValid = false;
        }
        
        // Disable submit button if invalid
        submitButton.disabled = !isValid;
        
        return isValid;
    }

    buyNowModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    stockQuantity = parseInt(button.getAttribute('data-stock-quantity'));
    document.getElementById('availableStock').textContent = stockQuantity;
    
    // Reset form state when opening
    quantityInput.classList.remove('is-invalid');
    quantityInput.value = 1;
    submitButton.disabled = false;
});
});
