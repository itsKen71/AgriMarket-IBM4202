<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 处理直接从产品页Buy Now过来的请求
    if (isset($_POST["product_id"]) && isset($_POST["quantity"])) {
        $selected_products = [
            [
                'product_id' => $_POST["product_id"],
                'quantity' => $_POST["quantity"]
            ]
        ];
    } 
    // 处理从购物车过来的请求
    elseif (isset($_POST["selected_products"])) {
        $selected_products = json_decode($_POST["selected_products"], true);
    }
    else {
        die("No products selected.");
    }

    // 现在$selected_products总是包含产品数组
    print_r($selected_products); // 调试用，实际处理订单逻辑
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Check Out</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\logo.png">
    <!-- Put CSS & JS Link Here-->
    <link rel="stylesheet" href="../../css/check_out.css">

</head>

<body class="check_out">
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <!-- Content Start Here -->



    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>