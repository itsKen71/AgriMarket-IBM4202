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

