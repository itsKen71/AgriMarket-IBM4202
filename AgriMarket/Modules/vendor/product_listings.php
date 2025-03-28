<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Listings</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/product_listings.css">
</head>

<body class="product_listings">
    <?php include '../../includes/header.php'; ?>

    <div class="container mt-5">
        <!-- Content Start Here -->
        <h2 class="mb-4">Product Listings</h2>
        <div class="accordion" id="productAccordion">
            <!-- Products Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingProducts">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="true" aria-controls="collapseProducts">
                        Products
                    </button>
                </h2>
                <div id="collapseProducts" class="accordion-collapse collapse show" aria-labelledby="headingProducts" data-bs-parent="#productAccordion">
                    <div class="accordion-body">
                        <!-- product listing  -->
                    </div>
                </div>
            </div>

            <!-- Pending Requests Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPending">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePending" aria-expanded="false" aria-controls="collapsePending">
                        Pending Requests
                    </button>
                </h2>
                <div id="collapsePending" class="accordion-collapse collapse" aria-labelledby="headingPending" data-bs-parent="#productAccordion">
                    <div class="accordion-body">
                        <!-- pending request list  -->
                    </div>
                </div>
            </div>

            <!-- Rejected Products Section -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingRejected">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRejected" aria-expanded="false" aria-controls="collapseRejected">
                        Rejected
                    </button>
                </h2>
                <div id="collapseRejected" class="accordion-collapse collapse" aria-labelledby="headingRejected" data-bs-parent="#productAccordion">
                    <div class="accordion-body">
                        <!-- rejected product list  -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Button -->
      <img src="../../Assets/img/add-circle.png" alt="Add Product" class="add-btn" id="addProductBtn">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
