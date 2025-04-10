<?php
session_start();

include '../../includes/database.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../../Modules/authentication/login.php"); // Redirect to login page
    exit(); // 
}

$orderHistory = getOrderHistoryByUser($user_id, $conn);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
      <?php $allRefunded = array_reduce($order['products'], function ($carry, $product) {
            return $carry && $product['status'] === 'Refunded';
            }, true);?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0">
                    Tracking Number: <span class="text-primary"><?= $order['tracking_number'] ?></span> |
                    <small class="text-muted"><?= $order['order_date'] ?></small>
                </h5>
                <div class="d-flex align-items-center">
                  <button class="btn btn-outline-danger btn-sm btn-refundWhole me-2"
                  <?= $allRefunded ? 'disabled' : '' ?>>
                    Refund All
                  </button>
                  <button class="btn btn-outline-success btn-sm btn-reorderWhole"
                    data-order-id="<?= $orderId ?>"
                    data-order-products='<?= json_encode($order['products']) ?>'>
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
                        <tr>
                            <td class="d-flex justify-content-between align-items-center">
                            <span class="<?= $product['status'] === 'Refunded' ? 'text-danger text-decoration-line-through fw-bold' : '' ?>">
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
                                data-product-category="<?php echo $product['category_name'] ?>"
                                >
                                    Preview
                                </button>
                            </td>
                            <td><?php echo number_format($product['unit_price'], 2) ?></td>
                            <td><?php echo $product['quantity'] ?></td>
                            <td><?php echo number_format($product['sub_price'] , 2) ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                <button class="btn btn-outline-danger btn-sm btn-refund">
                                    Refund
                                </button>
                                <button class="btn btn-outline-primary btn-sm btn-review"
                                data-product-id="<?php echo $product['product_id'] ?>"
                                data-product-name="<?php echo htmlspecialchars($product['product_name']) ?>"
                                data-product-image="../../<?php echo htmlspecialchars($product['product_image']) ?>"
                                data-review-rating="<?php echo $hasReview ? $reviewData['rating'] : 1 ?>"
                                data-review-description="<?php echo $hasReview ? htmlspecialchars($reviewData['review_description']) : '' ?>">
                                    Review
                                </button>
                                <button class="btn btn-outline-success btn-sm btn-reorder">
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
                              <?= number_format(array_reduce($order['products'], function($carry, $item) {
                                    return $carry + ($item['unit_price'] * $item['quantity']);
                                }, 0), 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Product Preview Modal -->
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

<!-- Product Review Modal -->
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
            <img id="reviewProductImage" src="" class="img-fluid rounded" style="max-height: 150px;" alt="Product Image">
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
            <textarea class="form-control" name="review_description" id="reviewText" rows="3" placeholder="Write your review here..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit Review</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Review Success Modal -->
<div class="modal fade" id="reviewSuccessModal" tabindex="-1" aria-labelledby="reviewSuccessModalLabel" aria-hidden="true">
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


    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>