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
                         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
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
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
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
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal">
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Confirm Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>