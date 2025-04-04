<?php
session_start();
include '..\..\includes\database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $_SESSION['selected_product_id'] = $_POST['product_id'];
    header("Location: product_page.php");
    exit();
}

if (!isset($_SESSION['selected_product_id'])) {
    header("Location: main_page.php");
    exit();
}

$product_id = $_SESSION['selected_product_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Product Page</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/product_page.css">

</head>

<body class="product_page">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->

        <!-- Testing  -->
        <h1>Selected Product ID: <?php echo htmlspecialchars($product_id); ?></h1>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>