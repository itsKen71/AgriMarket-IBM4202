<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Subscription Listings</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/subscription_listing.css">
    <script src="../../js/subscription.js"></script>
</head>

<body class="subscription_listing">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->
        <h2 class="subscription-title">Subscription Plans</h2>
        <div class="row">
            <!-- Tier I -->
             <div class="col-md-4">
                <div class="card text-center shadow-lg subscription-card">
                    <div class="card-header bg-primary text-white">
                        <h4>Tier I</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">$Free</h5> 
                        <div class="text-start"> 
                            <p class="card-text">&#10003 Upload 1 product at a time</p>
                            <p class="card-text">&#10007 Low stock alert</p>
                            <p class="card-text">&#10007 Personal staff contact</p>
                            <p class="card-text">&#10007 Access to analytic dashboard</p>
                        </div>
                    </div>
                    <div class="card-footer">
                    <button type="button" class="btn btn-primary" id="tier1Button">
                        Subscribe
                    </button>
                    </div>
                </div>
            </div>


            <!-- Tier II -->
            <div class="col-md-4">
                <div class="card text-center shadow-lg subscription-card">
                    <div class="card-header bg-success text-white">
                        <h4>Tier II</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">$9.99/month</h5> 
                        <div class="text-start"> 
                            <p class="card-text">&#10003 Upload 5 product at a time</p>
                            <p class="card-text">&#10003 Low stock alert</p>
                            <p class="card-text">&#10007 Personal staff contact</p>
                            <p class="card-text">&#10007 Access to analytic dashboard</p>
                        </div>
                    </div>
                    <div class="card-footer">
                    <button type="button" class="btn btn-success" id="tier2Button">
                        Subscribe
                    </button>
                    </div>
                </div>
            </div>

            <!-- Tier III -->
            <div class="col-md-4">
                <div class="card text-center shadow-lg subscription-card">
                    <div class="card-header bg-warning text-white">
                        <h4>Tier III</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">$39.99/month</h5> 
                        <div class="text-start"> 
                            <p class="card-text">&#10003 Upload 10++ product at a time</p>
                            <p class="card-text">&#10003 Low stock alert</p>
                            <p class="card-text">&#10003 Personal staff contact</p>
                            <p class="card-text">&#10003 Access to analytic dashboard</p>
                        </div>
                    </div>
                    <div class="card-footer">
                    <button type="button" class="btn btn-warning" id="tier3Button">
                        Subscribe
                    </button>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
             <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalTitle">Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Content  -->
                            <form id="paymentForm">
                                <!-- Select Payment Method -->
                                 <div class="mb-3">
                                    <label class="form-label">Select Payment Method:</label>
                                    <select class="form-select" id="paymentMethod" required>
                                        <option value="" disabled selected>Select a payment method</option>
                                        <option value="touchngo">Touch 'n Go</option>
                                        <option value="creditcard">Credit Card</option>
                                        <option value="onlinebanking">Online Banking</option>
                                    </select>
                                </div>

                            <!-- Select Subscription Duration -->
                            <div class="mb-3">
                                <label class="form-label">Select Subscription Duration (months):</label>
                                <input type="number" id="subscriptionMonths" class="form-control" min="1" max="12" value="1" required>
                            </div>
                            <!-- Display Subscription Details -->
                               <div class="mb-3">
                                <h5>Subscription Details</h5>
                                <p><strong>Start Date:</strong> <span id="startDate"></span></p>
                                <p><strong> End Date :</strong> <span id="endDate"></span></p>
                                <p><strong>Total Price:</strong> $ <span id="totalPrice">0.00</span></p>
                            </div>
                            </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="confirmPaymentButton">Confirm Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Success Modal -->
             <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="successModalLabel">Subscription Successful</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="subscriptionSuccessText">
                            <!-- Success message -->
                            </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

</div>

<!-- Warning Modal for Missing Payment Method -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningModalLabel">Payment Method Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Please select a payment method before confirming your subscription.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>