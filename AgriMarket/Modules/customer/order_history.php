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
    $noOrderMessage = "You have no order history yet.";
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
</head>

<body class="order_history">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->
        <h2 class="mb-4">Order History</h2>

    <?php if (isset($noOrderMessage)): ?>
        <!-- Display when there are no orders -->
        <div class="alert alert-info" role="alert">
            <?= $noOrderMessage ?>
        </div>
    <?php else: ?>
        <!-- Loop order if there are orderHistory -->
        <?php foreach ($orderHistory as $orderId => $order): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order_ID: <?= $orderId ?> | <small><?= $order['order_date'] ?></small></h5>
                    <button class="btn btn-success btn-sm ms-auto">Reorder</button>
                </div>
                <div class="card-body">
                    <!-- Loopeach product in the order -->
                    <table class="table table-bordered table-striped">
                        <thead class="table-success">
                            <tr>
                                <th>Product Name</th>
                                <th>Unit Price (RM)</th>
                                <th>Quantity</th>
                                <th>Total Price (RM)</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['products'] as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td><?= number_format($product['unit_price'], 2) ?></td>
                                    <td><?= $product['quantity'] ?></td>
                                    <td><?= number_format($product['unit_price'] * $product['quantity'], 2) ?></td>
                                    <td class="w-25">
                                        <!-- Preview, Refund, and Reorder Buttons for each product -->
                                        <div class="d-flex justify-content-center align-items-center w-100">
                                            <button class="btn btn-success btn-sm mx-2">Preview</button> 
                                            <button class="btn btn-success btn-sm mx-2">Refund</button> 
                                            <button class="btn btn-success btn-sm mx-2">Reorder</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>