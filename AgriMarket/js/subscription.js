document.addEventListener("DOMContentLoaded", function () {
    // Attach event listeners to subscription buttons
    document.getElementById("tier1Button").addEventListener("click", function () {
        selectSubscription("tier1");
    });

    document.getElementById("tier2Button").addEventListener("click", function () {
        selectSubscription("tier2");
    });

    document.getElementById("tier3Button").addEventListener("click", function () {
        selectSubscription("tier3");
    });

    // Attach event listener to update price and end date when duration changes
    document.getElementById("subscriptionMonths").addEventListener("input", function () {
        updateSubscriptionDetails();
    });

    // Attach event listener for payment confirmation
    document.getElementById("confirmPayment").addEventListener("click", confirmPayment);
});

let selectedPlan = null;

// Function to handle subscription selection
function selectSubscription(plan) {
    selectedPlan = plan;

    if (plan === "tier1") {
        // Show success modal for Tier I (free plan)
        let successModal = new bootstrap.Modal(document.getElementById("successModal"));
        successModal.show();
    } else {
        // Reset subscription duration input to 1 month by default
        document.getElementById("subscriptionMonths").value = 1;

        // Ensure the event listener updates values when the modal opens
        updateSubscriptionDetails();

        // Show payment modal
        let paymentModal = new bootstrap.Modal(document.getElementById("paymentModal"));
        paymentModal.show();
    }
}

// Function to update subscription price and end date dynamically
function updateSubscriptionDetails() {
    let monthsInput = document.getElementById("subscriptionMonths");
    let startDateElement = document.getElementById("startDate");
    let endDateElement = document.getElementById("endDate");
    let totalPriceElement = document.getElementById("totalPrice");

    if (!monthsInput || !startDateElement || !endDateElement || !totalPriceElement) {
        console.error("One or more elements are missing.");
        return;
    }

    let months = parseInt(monthsInput.value) || 1;
    let pricePerMonth = 0;

    if (selectedPlan === "tier2") {
        pricePerMonth = 9.99;
    } else if (selectedPlan === "tier3") {
        pricePerMonth = 39.99;
    }

    let totalPrice = pricePerMonth * months;
    totalPriceElement.textContent = totalPrice.toFixed(2);

    // Calculate start and end dates
    let startDate = new Date();
    let endDate = new Date();
    endDate.setMonth(startDate.getMonth() + months);

    // Format as YYYY-MM-DD
    startDateElement.textContent = startDate.toISOString().split("T")[0];
    endDateElement.textContent = endDate.toISOString().split("T")[0];

    console.log(`Updated Subscription: ${selectedPlan} - ${months} months`);
}

// Function to confirm payment
function confirmPayment() {
    let paymentMethod = document.getElementById("paymentMethod").value;
    let months = parseInt(document.getElementById("subscriptionMonths").value);

    if (!paymentMethod) {
        alert("Please select a payment method.");
        return;
    }

    alert(`Payment successful! You are now subscribed to ${selectedPlan.toUpperCase()} for ${months} month(s).`);

    // Close the payment modal after payment confirmation
    let paymentModal = bootstrap.Modal.getInstance(document.getElementById("paymentModal"));
    paymentModal.hide();
}
