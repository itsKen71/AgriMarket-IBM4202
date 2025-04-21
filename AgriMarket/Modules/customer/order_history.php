<?php
session_start();

include '../../includes/database.php'; // Include the database connection file

$db = new Database();
$customerClass = new Customer($db);
$paymentClass = new Payment($db);
$productClass = new Product($db);

$user_id = $_SESSION['user_id'] ?? null;

// Redirect to the login page if the user is not logged in
if (!$user_id) {
  header("Location: ../../Modules/authentication/login.php"); // Redirect to login page
  exit();
}

// Fetch the user's order history
$orderHistory = $customerClass->getOrderHistoryByUser($user_id);

// Loop through each order in the order history
foreach ($orderHistory as $orderId => $order) {
  // Check if the order is completed or delivered and the estimated delivery date has passed
  if (
    ($order['order_status'] === 'Completed' || $order['tracking_number'] === 'Delivered') &&
    strtotime($order['estimated_delivery_date']) <= time()
  ) { // Use estimated_delivery_date from shipment
    $payment = $paymentClass->getPaymentDetails($orderId); // Fetch payment details for the order
    // If the payment method is Cash On Delivery and the payment status is not completed, update it
    if ($payment && $payment['payment_method'] === 'Cash On Delivery' && $payment['payment_status'] !== 'Completed') {
      $paymentClass->updatePaymentStatus($payment['payment_id'], 'Completed'); // Update payment status to completed
    }
  }
}

// Fetch shipment details and update refund eligibility
foreach ($orderHistory as $orderId => $order) {
  // Fetch shipment details for the order using tracking_number and user_id
  $shipment = $productClass->getShipmentDetails($order['tracking_number'], $user_id);

  // Check if the current date is greater than or equal to the estimated delivery date
  $canRefund = strtotime($shipment['estimated_delivery_date']) <= time();

  foreach ($order['products'] as &$product) {
      // Remove the can_refund variable and handle the logic directly in the HTML
  }
}

// If no orders exist, set a message to display
if (empty($orderHistory)) {
  $noOrderMessage = "You have no order history yet...";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriMarket - Order History</title>
  <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/order_history.css">
  <script src="../../js/order_history.js"></script>
</head>

<body class="order_history">
  <?php include '../../includes/header.php'; ?>
  <div class="container mt-5">
    <!-- Order History Title -->
    <h2 class="mb-4">Order History</h2>
    <?php if (isset($noOrderMessage)): ?>
      <!-- Display when there are no orders -->
      <div class="alert alert-info" role="alert">
        <div class="custom-message"><?= $noOrderMessage ?></div>
      </div>
    <?php else: ?>
      <!-- Loop through orders if there are any -->
      <?php foreach ($orderHistory as $orderId => $order): ?>
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">
              Tracking Number: <span class="text-primary"><?= $order['tracking_number'] ?></span> |
              <small class="text-muted"><?= $order['order_date'] ?></small>
            </h5>
            <div class="d-flex align-items-center">
              <?php
              $shipment = $productClass->getShipmentDetails($order['tracking_number'], $user_id);
              $canRefundAll = strtotime($shipment['estimated_delivery_date']) <= time();

              $hasRefundedProduct = count(array_filter($order['products'], fn($p) => $p['status'] === 'Refunded')) > 0;
              $hasRefundRecord = count(array_filter($order['products'], fn($p) => !empty($p['refund_id']))) > 0;
              $disableRefundAll = !$canRefundAll || $hasRefundedProduct || $hasRefundRecord;

              $refundTooltip = '';
              if (!$canRefundAll) {
                  $refundTooltip = 'Product not Delivered.';
              } elseif ($hasRefundedProduct) {
                  $refundTooltip = 'One or more products are already refunded.';
              } elseif ($hasRefundRecord) {
                  $refundTooltip = 'Some products already have refund requests.';
              }

              $productsForModal = array_filter($order['products'], fn($p) => $p['status'] !== 'Refunded' && empty($p['refund_id']));
              ?>

              <div <?= $disableRefundAll ? 'data-bs-toggle="tooltip" title="' . htmlspecialchars($refundTooltip) . '"' : '' ?>>
                <button class="btn btn-outline-danger btn-sm btn-refund-all me-2" <?= $disableRefundAll ? 'disabled' : '' ?>
                  data-order-id="<?= $orderId ?>" data-payment-id="<?= $order['payment_id'] ?>"
                  data-user-id="<?= $user_id ?>" data-products='<?= json_encode(array_values($productsForModal)) ?>'>
                  Refund All
                </button>
              </div>
              <button class="btn btn-outline-success btn-sm btn-reorder-all me-2"
                data-products='<?= json_encode($order['products']) ?>'>
                Reorder All
              </button>
            </div>
          </div>
          <div class="card-body">
            <!-- Loop through each product in the order -->
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <th class="productNameColumn">Product Name</th>
                  <th>Unit Price (RM)</th>
                  <th>Quantity</th>
                  <th>Total Price (RM)</th>
                  <th class="w-25 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($order['products'] as $product): ?>
                  <?php
                  $paymentStatus = $order['payment_status'];
                  $productOrderStatus = $product['status'];
                  $refundRequested = !empty($product['refund_id']);
                  $shipment = $productClass->getShipmentDetails($order['tracking_number'], $user_id);
                  $canRefund = strtotime($shipment['estimated_delivery_date']) <= time();

                  $disableRefund = !$canRefund || $productOrderStatus === 'Refunded' || $refundRequested;
                  $tooltip = '';

                  if (!$canRefund) {
                      $tooltip = 'Product not Delivered.';
                  } elseif ($productOrderStatus === 'Refunded') {
                      $tooltip = 'Product already refunded.';
                  } elseif ($refundRequested) {
                      $tooltip = 'Refund already requested.';
                  }
                  ?>

                  <tr>
                    <td class="d-flex justify-content-between align-items-center">
                      <span
                        class="<?= $product['status'] === 'Refunded' ? 'text-danger text-decoration-line-through fw-bold' : '' ?>">
                        <?= htmlspecialchars($product['product_name']) ?>
                      </span>
                      <button class="btn btn-outline-success btn-sm btn-preview"
                        data-product-id="<?php echo $product['product_id'] ?>"
                        data-product-name="<?php echo htmlspecialchars($product['product_name']) ?>"
                        data-product-image="<?php echo '../../' . htmlspecialchars($product['product_image']) ?>"
                        data-product-quantity="<?php echo $product['stock_quantity'] ?>"
                        data-product-price="<?php echo $product['unit_price'] ?>"
                        data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                        data-product-weight="<?php echo $product['weight'] ?>"
                        data-product-category="<?php echo $product['category_name'] ?>">
                        Preview
                      </button>
                    </td>
                    <td><?php echo number_format($product['unit_price'], 2) ?></td>
                    <td><?php echo $product['quantity'] ?></td>
                    <td><?php echo number_format($product['sub_price'], 2) ?></td>
                    <td class="text-center">
                      <div class="btn-group">
                        <?php
                        $tooltipAttr = !empty($tooltip) ? 'data-bs-toggle="tooltip" title="' . htmlspecialchars($tooltip) . '"' : '';
                        ?>
                        <div class="d-inline-block" <?= $tooltipAttr ?>>
                          <button class="btn btn-outline-danger btn-sm btn-refund" <?= $disableRefund ? 'disabled' : '' ?>
                            data-product-id="<?= $product['product_id'] ?>" data-order-id="<?= $orderId ?>"
                            data-payment-id="<?= $order['payment_id'] ?>"
                            data-product-name="<?= htmlspecialchars($product['product_name']) ?>"
                            data-product-image="../../<?= htmlspecialchars($product['product_image']) ?>"
                            data-product-quantity="<?= $product['quantity'] ?>" data-sub-price="<?= $product['sub_price'] ?>">
                            Refund
                          </button>
                        </div>

                        <button class="btn btn-outline-primary btn-sm btn-review"
                          data-product-id="<?php echo $product['product_id'] ?>"
                          data-product-name="<?php echo htmlspecialchars($product['product_name']) ?>"
                          data-product-image="../../<?php echo htmlspecialchars($product['product_image']) ?>"
                          data-review-rating="<?php echo $hasReview ? $reviewData['rating'] : 1 ?>"
                          data-review-description="<?php echo $hasReview ? htmlspecialchars($reviewData['review_description']) : '' ?>">
                          Review
                        </button>
                        <button class="btn btn-outline-success btn-sm btn-reorder"
                          data-product-id="<?php echo $product['product_id'] ?>"
                          data-product-name="<?php echo htmlspecialchars($product['product_name']) ?>"
                          data-product-image="../../<?php echo htmlspecialchars($product['product_image']) ?>"
                          data-product-stock="<?php echo $product['stock_quantity'] ?>">
                          Reorder
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="4" class="fw-bold"></td>
                  <td class="text-center fw-bold">Total: RM
                    <?= number_format(array_reduce($order['products'], function ($carry, $item) {
                      return $carry + ($item['unit_price'] * $item['quantity']);
                    }, 0), 2) ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Product Preview Modal -->
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
              <img id="productPreviewImage" src="" alt="Product Image" class="img-fluid rounded border">
            </div>
            <div class="col-md-7">
              <h4 id="productPreviewName"></h4>
              <p><strong>Category:</strong> <span id="productPreviewCategory"></span></p>
              <p><strong>Weight:</strong> <span id="productPreviewWeight"></span></p>
              <p><strong>Price:</strong> <span id="productPreviewPrice"></span></p>
              <p><strong>Stock:</strong> <span id="productPreviewStock"></span></p>
              <p><strong>Description</strong><br> <span id="productPreviewDescription"></span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Review Modal -->
  <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="../../includes/submit_review.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="reviewModalLabel">Write a Review</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="product_id" id="reviewProductId">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <div class="text-center mb-3">
              <img id="reviewProductImage" src="" class="img-fluid rounded" style="max-height: 150px;"
                alt="Product Image">
              <h5 id="reviewProductName" class="mt-2"></h5>
            </div>
            <div class="mb-3 text-center">
              <label class="form-label"><strong>Rating:</strong></label><br>
              <div id="starRating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <input type="radio" class="btn-check" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i === 1 ? 'checked' : '' ?>>
                  <label class="btn btn-outline-warning" for="star<?= $i ?>"><?= $i ?> ★</label>
                <?php endfor; ?>
              </div>
            </div>
            <div class="mb-3">
              <label for="reviewText" class="form-label"><strong>Review:</strong></label>
              <textarea class="form-control" name="review_description" id="reviewText" rows="3"
                placeholder="Write your review here..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Submit Review</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reorder Modal -->
  <div class="modal fade" id="reorderModal" tabindex="-1" aria-labelledby="reorderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title" id="reorderModalLabel">Reorder Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="../../includes/reorder.php" method="POST">
          <div class="modal-body">
            <div class="row align-items-center">
              <div class="col-md-4 text-center mb-3 mb-md-0">
                <img id="reorderProductImage" src="" alt="Product Image" class="img-fluid rounded shadow-sm"
                  style="max-height: 150px;">
              </div>
              <div class="col-md-8">
                <h5 id="reorderProductName" class="mb-3 fw-semibold"></h5>
                <p>Available Stock: <span id="reorderStock" class="fw-bold text-success"></span></p>
                <input type="hidden" name="product_id" id="reorderProductId">
                <div class="mb-3">
                  <label for="reorderQuantity" class="form-label">Quantity</label>
                  <div class="input-group">
                    <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                    <input type="number" name="quantity" id="reorderQuantity" class="form-control text-center" min="1"
                      required>
                    <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary px-4">Add to Cart</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reorder All Modal -->
  <div class="modal fade" id="reorderAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form id="reorderAllForm">
          <div class="modal-header">
            <h5 class="modal-title">Reorder Items</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="reorderAllContent" style="max-height: 60vh; overflow-y: auto;">
            <!-- JS will populate this -->
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Add to Cart</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Refund Modal -->
  <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="refundForm" method="POST" action="process_refund.php">
          <div class="modal-header">
            <h5 class="modal-title" id="refundModalLabel">Refund Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="product_id" id="refundProductId">
            <input type="hidden" name="order_id" id="refundOrderId">
            <input type="hidden" name="payment_id" id="refundPaymentId">
            <input type="hidden" name="refund_amount" id="refundAmount">

            <div class="mb-3 text-center">
              <img id="refundProductImage" src="" class="img-fluid" style="max-height: 120px;" />
            </div>
            <p><strong>Product:</strong> <span id="refundProductName"></span></p>
            <p><strong>Quantity:</strong> <span id="refundProductQuantity"></span></p>
            <p><strong>Subtotal:</strong> RM <span id="refundProductSubPrice"></span></p>

            <div class="mb-3">
              <label for="refundReason" class="form-label">Reason</label>
              <textarea class="form-control" name="reason" id="refundReason" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Submit Refund</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Refund All Modal -->
  <div class="modal fade" id="refundAllModal" tabindex="-1" aria-labelledby="refundAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="refundAllModalLabel">Request Refund for All Products</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="refundAllForm">
          <input type="hidden" name="order_id" id="refundAllOrderId">
          <input type="hidden" name="payment_id" id="refundAllPaymentId">
          <input type="hidden" name="user_id" id="refundAllUserId">

          <div class="modal-body">
            <div id="refundAllProducts">
              <!-- Products will be inserted here -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Submit Refund</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Reorder Success Modal -->
  <div class="modal fade" id="reorderSuccessModal" tabindex="-1" aria-labelledby="reorderSuccessModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center p-4">
        <h5 class="modal-title" id="reorderSuccessModalLabel">Reorder Successful!</h5>
        <div class="modal-body">
          Product has been added to your cart.
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Review Success Modal -->
  <div class="modal fade" id="reviewSuccessModal" tabindex="-1" aria-labelledby="reviewSuccessModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewSuccessModalLabel">Review Submitted!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p>Thank you for your review. <br>Your feedback is valuable to us!</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Refund Success Modal -->
  <div class="modal fade" id="refundSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewSuccessModalLabel">Refund Submitted!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p>
            Your request is now submitted for review.<br>
            It takes up to 3 business days to process.
          </p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>