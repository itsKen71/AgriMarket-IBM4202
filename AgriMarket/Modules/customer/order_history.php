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
                                <button class="btn btn-outline-success btn-sm btn-preview" data-product-id="<?= $product['product_id'] ?>">
                                    Preview
                                </button>
                            </td>
                            <td><?= number_format($product['unit_price'], 2) ?></td>
                            <td><?= $product['quantity'] ?></td>
                            <td><?= number_format($product['unit_price'] * $product['quantity'], 2) ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                <button class="btn btn-outline-danger btn-sm btn-refund">
                                    Refund
                                </button>
                                <button class="btn btn-outline-primary btn-sm btn-review">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>