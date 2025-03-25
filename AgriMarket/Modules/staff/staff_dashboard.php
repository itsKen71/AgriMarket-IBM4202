<?php
session_start();
include '../../includes/database.php';

//Fetch Pending Request List
$pendingList = getPendingRequestList();

//Fetch Vendor Assistance List
$assisstanceList = getVendorAssisstanceList(21);

//Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {                     //Pending Request(Approve)
        $product_id = $_POST['product_id'];
        updatePendingRequestStatus($product_id, "Approved");
    } elseif (isset($_POST['reject'])) {               //Pending Request(Reject)
        $product_id = $_POST['product_id'];
        updatePendingRequestStatus($product_id, "Rejected");
    } elseif (isset($_POST['solve'])) {                    //Assisstance Request
        $request_id = $_POST['request_id'];
        updateAssisstanceRequestStatus($request_id, TRUE);
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
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
    <link rel="stylesheet" href="../../css/staff_dashboard.css">
    <script src="../../js/staff_dashboard.js"></script>

</head>

<body class="staff_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

    <!--Add Promotion Container-->
    <div class="Promotion">
        <p>Promotion Update
            <a href="../../Modules/staff/add_Promotion.php">
                <img src="../../Assets/img/addPromotion.png" alt="Add Promotion Button" style="width:30px; height:auto;" class="addPromotionBTN">
            </a>
        <p>
    </div>

        <!--Pending Listing---------------------------------------->
        <div class="Pending-Listing">
            <h1>Pending Request
                <button type="button" id="togglePending">▼</button>
            </h1>
            
            <!--Collapse Area-->
            <div id="pendingRequests" class="show">
                <div class="Pending-Listing-Container">
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
                                    <div class="Pending-Listing-Container-Footer">

                                        <button type="button" class="view_detail_pending_requestBTN"
                                            get-data-store="<?= htmlspecialchars($pending['store_name']); ?>"
                                            get-data-product="<?= htmlspecialchars($pending['product_name']); ?>"
                                            get-data-category="<?= htmlspecialchars($pending['category_name']); ?>"
                                            get-data-description="<?= htmlspecialchars($pending['description']); ?>"
                                            get-data-stock="<?= htmlspecialchars($pending['stock_quantity']); ?>"
                                            get-data-weight="<?= htmlspecialchars($pending['weight']); ?>"
                                            get-data-price="<?= htmlspecialchars($pending['unit_price']); ?>"
                                            onclick="showPendingDetails(this)"
                                        >View</button>

                                        <form method="POST">
                                            <input type="hidden" name="product_id" value="<?= $pending['product_id']; ?>">
                                            <button type="submit" name="approve" class="approve">Approve</button>
                                            <button type="submit" name="reject" class="reject">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="No-Data-Pending">
                            <h3>---No Product Pending Found---</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!--Pending Request Detail Dialog Container-->
        <div id="pendingRequestDetail_Container" class="Details_Container">
            <div class="Details_Container_Content">
                <span class="close" onclick="closePendingDetails()">&times;</span>
                <h2><strong>Pending Details</strong></h2>
                <div class="Details_Container_Content_Details">
                    <div class="Details_Label">Store Name</div>
                    <div class="Details_Value" id="show-store"></div>

                    <div class="Details_Label">Product Name</div>
                    <div class="Details_Value" id="show-product"></div>

                    <div class="Details_Label">Category</div>
                    <div class="Details_Value" id="show-category"></div>

                    <div class="Details_Label">Description</div>
                    <div class="Details_Value" id="show-description"></div>

                    <div class="Details_Label">Stock Quantity</div>
                    <div class="Details_Value" id="show-stock"></div>

                    <div class="Details_Label">Weight</div>
                    <div class="Details_Value"><span id="show-weight"></span> kg</div>

                    <div class="Details_Label">Unit Price</div>
                    <div class="Details_Value">RM<span id="show-price"></span></div>
                </div>
            </div>
        </div>

        <!--Vendor Assisstance Listing---------------------------------------->
        <div class="Assisstance-Listing">
            <h1>Assistance Request
                <button  type="button" id="toggleAssistance">▼</button>
            </h1>

            <!--Collapse Area-->
            <div id="assistanceRequests" class="collapse show">
                <div class="Assisstance-Listing-Container">
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
                                    <div class="Assisstance-Listing-Container-Footer">
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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>