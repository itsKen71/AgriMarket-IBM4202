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
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0">
                    Order ID: <span class="text-primary"><?= $orderId ?></span> |
                    <small class="text-muted"><?= $order['order_date'] ?></small>
                </h5>
                <button class="btn btn-outline-success btn-sm ms-auto btn-reorderWhole"
                    data-order-id="<?= $orderId ?>"
                    data-order-products='<?= json_encode($order['products']) ?>'>
                    Reorder All
                </button>
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
                                <span><?= htmlspecialchars($product['product_name']) ?></span>
                                <button class="btn btn-outline-success btn-sm btn-preview" data-product-id="<?= $product['product_id'] ?>">
                                    Preview
                                </button>
                            </td>
                            <td><?= number_format($product['unit_price'], 2) ?></td>
                            <td><?= $product['quantity'] ?></td>
                            <td><?= number_format($product['unit_price'] * $product['quantity'], 2) ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-danger btn-sm">Refund</button>
                                    <button class="btn btn-outline-primary btn-sm btn-review" data-product-id="<?= $product['product_id'] ?>"
                                        data-product-name="<?= htmlspecialchars($product['product_name']) ?>" data-product-image="../../<?= $product['product_image'] ?>">
                                        Review
                                    </button>
                                    <button class="btn btn-outline-success btn-sm btn-reorder" data-product-id="<?= $product['product_id'] ?>"
                                        data-stock="<?= $product['stock_quantity'] ?>">
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

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="reviewForm">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewModalLabel">Leave a Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="product_id" id="reviewProductId">
          <div class="text-center mb-3">
            <img id="reviewProductImage" src="" alt="Product Image" class="img-fluid rounded" style="max-height: 150px;">
            <h5 id="reviewProductName" class="mt-2"></h5>
          </div>
          <div class="mb-3 text-center">
            <label class="form-label">Rating:</label>
            <div id="reviewStars"></div><!-- Stars will be dynamically generated -->
            <input type="hidden" name="rating" id="ratingValue" value="1">
          </div>
          <div class="mb-3">
            <label for="reviewDescription" class="form-label">Review:</label>
            <textarea name="review_description" id="reviewDescription" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reorder Modal -->
<div class="modal fade" id="reorderModal" tabindex="-1" aria-labelledby="reorderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="reorderForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reorderModalLabel">Reorder Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="product_id" id="reorderProductId">
        <div class="mb-3">
          <label for="reorderQuantity" class="form-label">Quantity <span class="text-muted">(Available: <span id="availableStock">0</span>)</span></label>
          <input type="number" name="quantity" id="reorderQuantity" class="form-control" min="1" step="1" readonly onfocus="this.removeAttribute('readonly');" onkeydown="return false;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-reorderproduct">Add to Cart</button>
      </div>
    </form>
  </div>
</div>

<!-- Reorder Whole Order Modal -->
<div class="modal fade" id="reorderWholeModal" tabindex="-1" aria-labelledby="reorderWholeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="reorderWholeForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reorderWholeModalLabel">Reorder Entire Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="reorderWholeProductList"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-reorderwhole">Add to Cart</button>
      </div>
    </form>
  </div>
</div>

<!-- Review Success Modal -->
<div class="modal fade" id="reviewSuccessModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Review Submitted!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Thank you for your review. Your feedback is valuable to us!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reorder Success Modal -->
<div class="modal fade" id="reorderSuccessModal" tabindex="-1" aria-labelledby="reorderSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Reorder Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Product(s) added to cart successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>