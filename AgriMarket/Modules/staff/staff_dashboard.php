<?php
session_start();
include '../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

//Fetch Pending Request List
$pendingList = getPendingRequestList();

//////////////////////////////Dummy Function///////////////////////////////////////////////////
//Fetch Vendor Assistance List
$assisstanceList = getVendorAssisstanceList(7);

//Fetch review list
$reviewList = getReviewList();

//Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Pending Request(Approve)
    if (isset($_POST['approve'])) {
        $product_id = $_POST['product_id'];
        updatePendingRequestStatus($product_id, "Approved");

        //Pending Request(Reject)
    } elseif (isset($_POST['reject'])) {
        $product_id = $_POST['product_id'];
        updatePendingRequestStatus($product_id, "Rejected");

        //Assisstance Request
    } elseif (isset($_POST['solve'])) {
        $request_id = $_POST['request_id'];
        updateAssisstanceRequestStatus($request_id, TRUE);

        //Promotion Update
    } elseif (isset($_POST['discountCode'], $_POST['promotionTitle'], $_POST['promotionMessage'], $_POST['startDate'], $_POST['endDate'], $_POST['discountPercentage'], $_POST['minPurchaseAmount'])) {

        $discountCode = $_POST['discountCode'];
        $promotionTitle = $_POST['promotionTitle'];
        $promotionMessage = $_POST['promotionMessage'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $discountPercentage = $_POST['discountPercentage'];
        $minPurchaseAmount = $_POST['minPurchaseAmount'];
        $isActive = 1;
        //////////////////////////////Dummy Function///////////////////////////////////////////////////
        $created_by = 21;

        update_Promotion_Discount($discountCode, $promotionTitle, $promotionMessage, $startDate, $endDate, $discountPercentage, $minPurchaseAmount, $isActive, $created_by);
    }

    //Refresh Page to Avoid Press Button Twice
    header("Location: ../../Modules/staff/staff_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Staff Dashboard</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <link rel="stylesheet" href="../../css/staff_dashboard.css">
    <script src="../../js/staff_dashboard.js"></script>
</head>

<body class="staff_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

        <!--Promotion Update-->
        <div class="Promotion">
            <p>Promotion Update
                <img src="../../Assets/img/add-circle.png" alt="Add Promotion Button" style="width:30px; height:auto;cursor:pointer;" class="addPromotionBTN" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
            <p>
        </div>

        <!--Promotion Update Modal-->
        <div class="modal fade" id="addPromotionModal" tabindex="-1" aria-labelledby="addPromotionModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <!--Header-->
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPromotionModalTitle">Promotion Update</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!--Body-->
                    <div class="modal-body">
                        <form method="POST">
                            <!--Discount Code (Auto-Generate)-->
                            <div class="mb-3">
                                <label for="discountCode" class="form-label">Discount Code:</label>
                                <input type="text" class="form-control" id="discountCode" name="discountCode" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="promotiontitle" class="form-label">Promotion Title:</label>
                                <input type="text" class="form-control" id="promotionTitle" name="promotionTitle" required>
                            </div>

                            <div class="mb-3">
                                <label for="promotionMessage" class="form-label">Promotion Message:</label>
                                <input type="text" class="form-control" id="promotionMessage" name="promotionMessage" required>
                            </div>

                            <div class="mb-3">
                                <label for="startDate" class="form-label">Start Date:</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" required>
                            </div>

                            <div class="mb-3">
                                <label for="endDate" class="form-label">End Date:</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" required>
                            </div>

                            <div class="mb-3">
                                <label for="discountPercentage" class="form-label">Discount Percentage(%):</label>
                                <input type="number" class="form-control" id="discountPercentage" name="discountPercentage" min="1" max="100" required>
                            </div>

                            <div class="mb-3">
                                <label for="minPurchaseAmount" class="form-label">Minimum Purchase Amount(RM):</label>
                                <input type="number" class="form-control" id="minPurchaseAmount" name="minPurchaseAmount" min="1" required>
                            </div>

                            <!--Submit Button-->
                            <div class="text-center">
                                <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>


        <!--Listing-->
        <div class="accordion" id="accordionPanels">

            <!--Pending Listing-->
            <div class="accordion-item">
                <!--Header-->
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        <img src="../../Assets/img/pending.png" alt="pending icon" style="width:30px; height:auto;margin-right:10px;">
                        <strong>Pending Request</strong>
                    </button>
                </h2>

                <!--Toggle Area-->
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">

                    <!--Pending Card-->
                    <div class="accordion-body">
                        <?php if (!empty($pendingList)): ?>
                            <?php foreach ($pendingList as $pending): ?>

                                <!--Pending Card-->
                                <div class="Pending-Card">

                                    <!--Header Section(Display Store Name)--->
                                    <div class="Pending-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($pending['store_name']); ?></h2>
                                    </div>

                                    <!--Pending Request Body-->
                                    <div class="Pending-Card-Body">
                                        <!--Content Section(Display Quantity, Unit Price)-->
                                        <div class="Pending-Listing-Container-Content">
                                            <span class="label">Product Name</span> <span class="colon">:</span> <?= $pending['product_name']; ?>
                                            <span class="label">Stock Quantity</span> <span class="colon">:</span> <span class="value"><?= $pending['stock_quantity']; ?></span>
                                            <span class="label">Unit Price</span> <span class="colon">:</span> <span class="value">RM <?= $pending['unit_price']; ?></span>
                                        </div>

                                        <!--Button for Approve/ Decline Pending-->
                                        <div class="Pending-Listing-Container-Button">
                                            <form method="POST">
                                                <button type="button" class="btn btn-primary btn-sm btn-preview"
                                                    data-product-id="<?= $pending['product_id']; ?>">
                                                    Preview
                                                </button>
                                                <input type="hidden" name="product_id" value="<?= $pending['product_id']; ?>">
                                                <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                                <button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data-Pending">
                                <p>---No Product Pending Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!--Pending Request Detail Modal-->
            <div class="modal fade" id="pendingRequestModal" tabindex="-1" aria-labelledby="pendingRequestModalTitle" aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered " role="document">

                    <!--Content-->
                    <div class="modal-content">

                        <!--Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="pendingRequestModalTitle">Pending Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                        </div>

                        <!--Body-->
                        <div class="modal-body">

                            <div class="Details_Label"><strong> Name</strong></div>
                            <div class="Details_Value" id="show-store"></div>

                            <div class="Details_Label"><strong>Product Name</strong></div>
                            <div class="Details_Value" id="show-product"></div>

                            <div class="Details_Label"><strong>Category</strong></div>
                            <div class="Details_Value" id="show-category"></div>

                            <div class="Details_Label"><strong>Description</strong></div>
                            <div class="Details_Value" id="show-description"></div>

                            <div class="Details_Label"><strong>Stock Quantity</strong></div>
                            <div class="Details_Value" id="show-stock"></div>

                            <div class="Details_Label"><strong>Weight</strong></div>
                            <div class="Details_Value"><span id="show-weight"></span> kg</div>

                            <div class="Details_Label"><strong>Unit Price</strong></div>
                            <div class="Details_Value">RM<span id="show-price"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Product Preview Modal-->
            <div class="modal fade" id="productPreviewModal" tabindex="-1" aria-labelledby="productPreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productPreviewModalLabel">Product Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="productPreviewBody">
                            <div class="row">
                                <div class="col-md-5 text-center">
                                    <img id="productPreviewImage" src="" alt="Product Image" class="img-fluid rounded border">
                                </div>
                                <div class="col-md-7">
                                    <h4 id="productPreviewName"></h4>
                                    <p><strong>Category:</strong> <span id="productPreviewCategory"></span></p>
                                    <p><strong>Description:</strong> <span id="productPreviewDescription"></span></p>
                                    <p><strong>Stock:</strong> <span id="productPreviewStock"></span></p>
                                    <p><strong>Weight:</strong> <span id="productPreviewWeight"></span></p>
                                    <p><strong>Price:</strong> <span id="productPreviewPrice"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        <img src="../../Assets/img/request.png" alt="request icon" style="width:30px; height:auto;margin-right:10px;">
                        <strong>Request Assistance</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <?php if (!empty($assisstanceList)): ?>
                            <?php foreach ($assisstanceList as $assisstance): ?>

                                <!--Assisstance Card-->
                                <div class="Assisstance-Card">

                                    <!--Header Section(Display Store Name)--->
                                    <div class="Assisstance-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($assisstance['store_name']); ?></h2>
                                    </div>

                                    <!--Assisstance Body-->
                                    <div class="Assisstance-Card-Body">
                                        <!--Content Section(Display Request Description, Type, Date)-->
                                        <div class="Assisstance-Listing-Container-Content">
                                            <span class="label">Description</span> <span class="colon">:</span> <span class="value"><?= $assisstance['request_description']; ?></span>
                                            <span class="label">Type</span> <span class="colon">:</span> <span class="value"><?= $assisstance['request_type']; ?></span>
                                            <span class="label">Date& Time</span> <span class="colon">:</span> <span class="value"><?= $assisstance['request_date']; ?></span>
                                        </div>

                                        <!--Button for Mark Complete Request Assistance-->
                                        <div class="Assisstance-Listing-Container-Button">
                                            <form method="POST">
                                                <input type="hidden" name="request_id" value="<?= $assisstance['request_id']; ?>">
                                                <button type="submit" name="solve" class="solve">Complete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data-Assisstant">
                                <p>---No Assistant Request Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!--Customer review-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                        <img src="../../Assets/img/review.png" alt="review icon" style="width:30px; height:auto;margin-right:10px;">
                        <strong>Customer Review</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <?php if (!empty($reviewList)): ?>
                            <?php foreach ($reviewList as $review): ?>

                                <!--Review Card-->
                                <div class="Review-Card">

                                    <!--Header Section--->
                                    <div class="Review-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($review['Name']); ?></h2>
                                    </div>

                                    <!--Review Body-->
                                    <div class="Review-Card-Body">
                                        <!--Content Section-->
                                        <div class="Review-Listing-Container-Content">
                                            <span class="label">Product Name</span> <span class="colon">:</span> <span class="value"><?= $review['product_name']; ?></span>
                                            <span class="label">Review Description</span> <span class="colon">:</span> <span class="value"><?= $review['review_description']; ?></span>

                                            <span class="label">Rating</span> <span class="colon">:</span>
                                            <span class="value">
                                                <?php
                                                $rating = $review['rating'];
                                                // Display  rating using stars
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo "★"; 
                                                    } else {
                                                        echo "☆"; 
                                                    }
                                                }
                                                ?>
                                            </span>

                                            <span class="label">Review Date</span> <span class="colon">:</span> <span class="value"><?= $review['review_date']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data-Review">
                                <p>---No Review Request Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>