<?php
    session_start();
    include '../../includes/database.php';

    //Fetch Pending Request List
    $pendingList=getPendingRequestList();

    //Fetch Vendor Assistance List
    $assisstanceList=getVendorAssisstanceList(21);

    //Handle Form Submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['approve'])) {                     //Pending Request(Approve)
            $product_id = $_POST['product_id'];
            updatePendingRequestStatus($product_id, "Approved");
        } elseif (isset($_POST['reject'])) {               //Pending Request(Reject)
            $product_id = $_POST['product_id'];
            updatePendingRequestStatus($product_id, "Rejected");
        }elseif(isset($_POST['solve'])){                    //Assisstance Request
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
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/staff_dashboard.css">
    

</head>

<body class="staff_dashboard">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">

        <!--Pending Listing---------------------------------------->
        <div class="Pending-Listing">
            <h1>Pending Request Listing</h1>

            <div class="Pending-Listing-Container">
                <?php if (!empty($pendingList)): ?>
                    <?php foreach ($pendingList as $pending): ?>

                        <!--Pending Card-->
                        <div class="Pending-Card">

                            <!--Header Section(Display Store Name)--->
                            <div class="Pending-Listing-Container-Header">
                                <h2><?= htmlspecialchars($pending['store_name']); ?></h2>
                            </div>

                            <!--Content Section(Display Quantity, Unit Price)-->
                            <div class="Pending-Listing-Container-Content">
                                <p>Quantity: <?=$pending['stock_quantity']; ?></p>
                                <p>Unit Price:RM <?= $pending['unit_price']; ?></p>
                            </div>

                            <!--Button for Approve/ Decline Pending-->
                            <div class="Pending-Listing-Container-Footer">
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?= $pending['product_id']; ?>">
                                    <button type="submit" name="approve">Approve</button>
                                    <button type="submit" name="reject">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>---No Product Found---</h3>
                <?php endif; ?>
            </div>
        </div>

        <!--Vendor Assisstance Listing---------------------------------------->
        <div class="Assisstance-Listing">
            <h1>Assisstance Request Listing</h1>

            <div class="Assisstance-Listing-Container">
                <?php if (!empty($assisstanceList)): ?>
                    <?php foreach ($assisstanceList as $assisstance): ?>

                        <!--Assisstance Card-->
                        <div class="Assisstance-Card">

                            <!--Header Section(Display Store Name)--->
                            <div class="Assisstance-Listing-Container-Header">
                                <h2><?= htmlspecialchars($assisstance['store_name']); ?></h2>
                            </div>

                            <!--Content Section(Display Request Description, Type, Date)-->
                            <div class="Assisstance-Listing-Container-Content">
                                <p><?=$assisstance['request_description']; ?></p>
                                <p>Request Type: <?= $assisstance['request_type']; ?></p>
                                <p>Request Date: <?= $assisstance['request_date']; ?></p>
                            </div>

                            <!--Button for Mark Complete Request Assistance-->
                            <div class="Assisstance-Listing-Container-Footer">
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?= $assisstance['request_id']; ?>">
                                    <button type="submit" name="solve">Complete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>---No Assistant Found---</h3>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>