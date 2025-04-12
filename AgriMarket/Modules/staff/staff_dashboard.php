<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;

// Redirect to the login page if the user is not logged in
if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php");
    exit();
}

include '../../includes/database.php';
require '../../includes/PHPMailer/src/PHPMailer.php';
require '../../includes/PHPMailer/src/SMTP.php';
require '../../includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$customerClass = new Customer($db);
$vendorClass = new Vendor($db);
$staffClass = new Staff($db);
$paymentClass = new Payment($db);

// Fetch lists for pending requests, vendor assistance, and refunds
$pendingList = $staffClass->getPendingRequestList();
$assisstanceList = $vendorClass->getVendorAssisstanceList($user_id);
$refundList = $paymentClass->getRefundList();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    // Handle approval of pending product requests
    if ($_POST['action'] === 'approve_pending') {
        $staffClass->updatePendingRequestStatus($_POST['product_id'], "Approved");
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Handle rejection of pending product requests
    if ($_POST['action'] === 'reject_pending') {
        $staffClass->updatePendingRequestStatus($_POST['product_id'], "Rejected");
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Handle approval or rejection of refund requests
    if ($_POST['action'] === 'refund' && isset($_POST['refund_id'], $_POST['status'])) {
        $current_date = date("Y-m-d");
        $status = $_POST['status'];
        $request_id = $_POST['refund_id'];
        $paymentClass->updateRefund($request_id, $status, $current_date, $user_id);
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Mark assistance requests as completed
    if ($_POST['action'] === 'assistance' && isset($_POST['request_id'])) {
        $staffClass->updateAssisstanceRequestStatus($_POST['request_id'], TRUE);
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Handle invalid actions
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit();
}

// Handle promotion updates and email notifications
if (
    isset(
        $_POST['discountCode'],
        $_POST['promotionTitle'],
        $_POST['promotionMessage'],
        $_POST['startDate'],
        $_POST['endDate'],
        $_POST['discountPercentage'],
        $_POST['minPurchaseAmount']
    )
) {
    $currentDate = date("Y-m-d");

    // Promotion details
    $discountCode = $_POST['discountCode'];
    $promotionTitle = $_POST['promotionTitle'];
    $promotionMessage = $_POST['promotionMessage'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $discountPercentage = $_POST['discountPercentage'];
    $minPurchaseAmount = $_POST['minPurchaseAmount'];
    $isActive = 1;
    $created_by = $user_id;

    // Update promotion details in the database
    $staffClass->update_Promotion_Discount($discountCode, $promotionTitle, $promotionMessage, $startDate, $endDate, $discountPercentage, $minPurchaseAmount, $isActive, $created_by);

    // Fetch customer emails for sending promotion notifications
    $customers = $customerClass->getCustomerEmails();

    if ($customers) {
        foreach ($customers as $customer) {
            $email = $customer['email'];
            $fullName = $customer['full_name'];

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kenjichong88@gmail.com'; // Replace with your Gmail
                $mail->Password = 'zqrg tdtj lxrv lgpk'; // Replace with your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('agrimarketonline24@gmail.com', 'AgriMarket');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Promotion Update - AgriMarket';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                        <div style='text-align: center; padding: 20px; background-color: #f5f1eb; border-bottom: 2px solid #c9d8b6;'>
                            <h2 style='color: #4CAF50; margin: 0;'>AgriMarket</h2>
                            <p style='margin: 0; font-size: 14px;'>Your trusted agricultural marketplace</p>
                        </div>
                        <div style='padding: 20px;'>
                            <p><strong>Dear $fullName,</strong></p>
                            <h3 style='color: #4CAF50;'>$promotionTitle</h3>
                            <p>$promotionMessage</p>
                            <p>You are encouraged to use the discount code below to purchase goods to earn a <strong>$discountPercentage%</strong> discount with a minimum purchase of RM <strong>$minPurchaseAmount</strong>:</p>
                            <div style='text-align: center; margin: 20px 0;'>
                                <span style='font-size: 24px; font-weight: bold; color: #4CAF50; padding: 10px 20px; border: 2px dashed #4CAF50; display: inline-block;'>
                                    $discountCode
                                </span>
                            </div>
                            <p><strong>Important Notes:</strong><br>
                            Please make sure to apply the discount code at checkout. The promotion is subject to terms and conditions and may have an expiration date.</p>
                            <p>Thank you for being a valued customer of AgriMarket!</p>
                            <p>Best regards,<br>The AgriMarket Team</p>
                        </div>
                        <div style='text-align: center; padding: 10px; background-color: #f5f1eb; border-top: 2px solid #c9d8b6; font-size: 12px; color: #777;'>
                            <p style='margin: 0;'>© 2025 AgriMarket. All rights reserved.</p>
                        </div>
                    </div>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error for $email: " . $mail->ErrorInfo);
            }
        }
    } else {
        echo "Failed to retrieve customer emails from the database.";
    }
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
                <img src="../../Assets/img/add-circle.png" alt="Add Promotion Button"
                    style="width:30px; height:auto;cursor:pointer;" class="addPromotionBTN" data-bs-toggle="modal"
                    data-bs-target="#addPromotionModal">
            </p>
        </div>

        <!--Promotion Update Modal-->
        <div class="modal fade" id="addPromotionModal" tabindex="-1" aria-labelledby="addPromotionModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <!--Header-->
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPromotionModalTitle">Promotion Update</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!--Body-->
                    <div class="modal-body">
                        <form id="promotionForm" method="POST">
                            <!--Discount Code (Auto-Generate)-->
                            <div class="mb-3">
                                <label for="discountCode" class="form-label">Discount Code:</label>
                                <input type="text" class="form-control" id="discountCode" name="discountCode" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="promotionTitle" class="form-label">Promotion Title:</label>
                                <input type="text" class="form-control" id="promotionTitle" name="promotionTitle"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="promotionMessage" class="form-label">Promotion Message:</label>
                                <input type="text" class="form-control" id="promotionMessage" name="promotionMessage"
                                    required>
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
                                <input type="number" step="0.01" class="form-control" id="discountPercentage"
                                    name="discountPercentage" min="1" max="100" required>
                            </div>

                            <div class="mb-3">
                                <label for="minPurchaseAmount" class="form-label">Minimum Purchase Amount(RM):</label>
                                <input type="number" step="0.01" class="form-control" id="minPurchaseAmount"
                                    name="minPurchaseAmount" min="1" required>
                            </div>

                            <!--Submit Button-->
                            <div class="text-center">
                                <button type="submit" id="submit-button" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true" id="submit-spinner"></span>
                                    <span id="submit-text">Update</span>
                                </button>

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
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                        aria-controls="panelsStayOpen-collapseOne">
                        <img src="../../Assets/img/pending.png" alt="pending icon"
                            style="width:30px; height:auto;margin-right:10px;">
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
                                <div class="Pending-Card" data-product-id="<?= $pending['product_id']; ?>">

                                    <!--Header Section(Display Store Name)--->
                            <div class="Pending-Listing-Container-Header">
                                <h3>
                                    <?= htmlspecialchars($pending['store_name']); ?>
                                </h3>
                            </div>

                            <!--Pending Request Body-->
                                    <div class="Pending-Card-Body">
                                        <div class="Pending-Listing-Container-Content">
                                            <span class="label">Product Name</span> <span class="colon">:</span>
                                            <?= $pending['product_name']; ?>
                                            <span class="label">Stock Quantity</span> <span class="colon">:</span> <span
                                                class="value"><?= $pending['stock_quantity']; ?></span>
                                            <span class="label">Unit Price</span> <span class="colon">:</span> <span
                                                class="value">RM <?= $pending['unit_price']; ?></span>
                                        </div>

                                        <!-- Button for Approve/ Decline Pending -->
                                        <div class="Pending-Listing-Container-Button">
                                            <button type="button" class="btn btn-primary btn-sm btn-preview"
                                                data-product-id="<?= $pending['product_id']; ?>"
                                                data-product-name="<?= htmlspecialchars($pending['product_name']); ?>"
                                                data-product-image="<?= htmlspecialchars($pending['product_image']); ?>"
                                                data-product-category="<?= htmlspecialchars($pending['category_name'] ?? 'Unknown Category'); ?>"
                                                data-product-description="<?= htmlspecialchars($pending['description']); ?>"
                                                data-product-stock="<?= $pending['stock_quantity']; ?>"
                                                data-product-weight="<?= $pending['weight']; ?>"
                                                data-product-price="<?= $pending['unit_price']; ?>">
                                                Preview
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="updatePendingStatus(<?= $pending['product_id']; ?>, 'approve_pending')">
                                                Approve
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="updatePendingStatus(<?= $pending['product_id']; ?>, 'reject_pending')">
                                                Reject
                                            </button>

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
            <div class="modal fade" id="pendingRequestModal" tabindex="-1" aria-labelledby="pendingRequestModalTitle"
                aria-hidden="true">

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
            <div class="modal fade" id="productPreviewModal" tabindex="-1" aria-labelledby="productPreviewModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productPreviewModalLabel">Product Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="productPreviewBody">
                            <div class="row">
                                <div class="col-md-5 text-center">
                                    <img id="productPreviewImage" src="" alt="Product Image"
                                        class="img-fluid rounded border">
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

            <!--Refund List-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false"
                        aria-controls="panelsStayOpen-collapseThree">
                        <img src="../../Assets/img/refund.png" alt="refund icon"
                            style="width:35px; height:auto;margin-right:10px;">
                        <strong>Refund Listing</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <?php if (!empty($refundList)): ?>
                            <?php foreach ($refundList as $refund): ?>

                                <!--Refund Card-->
                                <div class="Refund-Card" data-refund-id="<?= $refund['refund_id']; ?>">

                                    <!--Header Section--->
                            <div class="Refund-Listing-Container-Header">
                                <h3>Order ID :
                                    <?= htmlspecialchars($refund['order_id']); ?>
                                </h3>
                            </div>

                            <!--Refund Body-->
                                    <div class="Refund-Card-Body">
                                        <div class="Refund-Listing-Container-Content">
                                            <span class="label">Product Name</span> <span class="colon">:</span> <span
                                                class="value"><?= $refund['product_name']; ?></span>
                                            <span class="label">Refund Amount</span> <span class="colon">:</span> <span
                                                class="value">RM<?= $refund['refund_amount']; ?></span>
                                            <span class="label">Refund Date</span> <span class="colon">:</span> <span
                                                class="value"><?= $refund['refund_date']; ?></span>
                                        </div>

                                        <!--Approve / Reject Buttons-->
                                        <div class="Refund-Listing-Container-Button">
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="updateRefundStatus(<?= $refund['refund_id']; ?>, 'Approved')">Approve</button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="updateRefundStatus(<?= $refund['refund_id']; ?>, 'Rejected')">Reject</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="No-Data-Refund">
                                <p>---No Refund Found---</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!--Request Assistance List-->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false"
                        aria-controls="panelsStayOpen-collapseTwo">
                        <img src="../../Assets/img/request.png" alt="request icon"
                            style="width:30px; height:auto;margin-right:10px;">
                        <strong>Request Assistance</strong>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <?php if (!empty($assisstanceList)): ?>
                            <?php foreach ($assisstanceList as $assisstance): ?>

                                <!--Assistance Card-->
                                <div class="Assisstance-Card" data-request-id="<?= $assisstance['request_id']; ?>">

                                    <!--Header Section-->
                                    <div class="Assisstance-Listing-Container-Header">
                                        <h2><?= htmlspecialchars($assisstance['store_name']); ?></h2>
                                    </div>

                                    <!--Assistance Body-->
                                    <div class="Assisstance-Card-Body">
                                        <div class="Assisstance-Listing-Container-Content">
                                            <span class="label">Description</span> <span class="colon">:</span> <span
                                                class="value"><?= $assisstance['request_description']; ?></span>
                                            <span class="label">Type</span> <span class="colon">:</span> <span
                                                class="value"><?= $assisstance['request_type']; ?></span>
                                            <span class="label">Date& Time</span> <span class="colon">:</span> <span
                                                class="value"><?= $assisstance['request_date']; ?></span>
                                        </div>

                                        <!--Mark as Complete Button-->
                                        <div class="Assisstance-Listing-Container-Button">
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="markAssistanceComplete(<?= $assisstance['request_id']; ?>)">Complete</button>
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
    </div>

    <?php include '../../includes/footer_2.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>