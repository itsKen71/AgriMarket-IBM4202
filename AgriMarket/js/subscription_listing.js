document.addEventListener("DOMContentLoaded", function () {
    setupSubscriptionButtons();
    setupPaymentConfirmation();
    setupMonthInputListener();
    handleSubscriptionSuccessFromURL();
});

let selectedPlan = null;
let selectedMonths = 1;

// Button Handlers
function setupSubscriptionButtons() {
    const buttonMap = {
        tier1Button: "tier1",
        tier2Button: "tier2",
        tier3Button: "tier3"
    };

    Object.entries(buttonMap).forEach(([id, plan]) => {
        const button = document.getElementById(id);
        if (button) {
            button.addEventListener("click", () => selectSubscription(plan));
        }
    });
}

function setupPaymentConfirmation() {
    const confirmBtn = document.getElementById("confirmPaymentButton");
    if (confirmBtn) {
        confirmBtn.addEventListener("click", confirmPayment);
    }
}

function setupMonthInputListener() {
    const input = document.getElementById("subscriptionMonths");
    if (input) {
        input.addEventListener("input", updateSubscriptionDetails);
    }
}

// Subscription Logic
function selectSubscription(plan) {
    selectedPlan = plan;

    if (plan === "tier1") {
        submitForm("1", 1);
    } else {
        document.getElementById("subscriptionMonths").value = 1;
        updateSubscriptionDetails();

        new bootstrap.Modal(document.getElementById("paymentModal")).show();
    }
}

function submitForm(planId, months) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../../includes/subscribe.php";

    form.innerHTML = `
        <input type="hidden" name="plan_id" value="${planId}">
        <input type="hidden" name="subscription_months" value="${months}">
    `;

    document.body.appendChild(form);
    form.submit();
}


//Payment Flow
function confirmPayment() {
    const paymentMethod = document.getElementById("paymentMethod").value;
    if (!paymentMethod) {
        new bootstrap.Modal(document.getElementById("warningModal")).show();
        return;
    }

    if (!selectedPlan) {
        console.error("No subscription plan selected.");
        return;
    }

    const monthsInput = document.getElementById("subscriptionMonths");
    const months = parseInt(monthsInput?.value) || 1;

    const planMap = {
        tier1: "Tier I",
        tier2: "Tier II",
        tier3: "Tier III"
    };

    document.getElementById("subscriptionSuccessText").innerHTML = `
        You have successfully subscribed to <strong>${planMap[selectedPlan]}</strong> for <strong>${months} month(s)</strong>.
    `;

    const paymentModal = bootstrap.Modal.getInstance(document.getElementById("paymentModal"));
    paymentModal?.hide();

    const planId = selectedPlan === "tier2" ? "2" : "3";
    submitForm(planId, months);

    new bootstrap.Modal(document.getElementById("successModal")).show();
}

// Subscription Details
function updateSubscriptionDetails() {
    const monthsInput = document.getElementById("subscriptionMonths");
    const startDateElement = document.getElementById("startDate");
    const endDateElement = document.getElementById("endDate");
    const totalPriceElement = document.getElementById("totalPrice");

    const months = parseInt(monthsInput?.value) || 1;
    selectedMonths = months;

    const prices = {
        tier2: 9.99,
        tier3: 39.99
    };

    const pricePerMonth = prices[selectedPlan] || 9.99;
    totalPriceElement.textContent = (pricePerMonth * months).toFixed(2);

    const startDate = new Date();
    const endDate = new Date();
    endDate.setMonth(startDate.getMonth() + months);

    startDateElement.textContent = startDate.toISOString().split("T")[0];
    endDateElement.textContent = endDate.toISOString().split("T")[0];
}


//  Handle Redirect Success
function handleSubscriptionSuccessFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get("subscribe");
    const planParam = urlParams.get("plan");
    const monthsParam = urlParams.get("months");

    if (success === "success" && planParam && monthsParam) {
        const planMap = {
            tier1: "Tier I",
            tier2: "Tier II",
            tier3: "Tier III"
        };

        const displayPlan = planMap[planParam] || "Your Plan";
        const displayMonths = parseInt(monthsParam) || 1;

        let message = "";

        if (planParam === "tier1") {
            message = `You have successfully subscribed to <strong>${displayPlan}</strong>.`;
        } else {
            message = `You have successfully subscribed to <strong>${displayPlan}</strong> for <strong>${displayMonths} month(s)</strong>.`;
        }

        document.getElementById("subscriptionSuccessText").innerHTML = message;

        new bootstrap.Modal(document.getElementById("successModal")).show();

        // Clean URL
        urlParams.delete('subscribe');
        urlParams.delete('plan');
        urlParams.delete('months');
        window.history.replaceState({}, '', window.location.pathname);
    }
}
