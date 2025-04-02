<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_products = json_decode($_POST["selected_products"], true);

    if (empty($selected_products)) {
        die("No products selected.");
    }

    print_r($selected_products); // 这里可以处理逻辑，比如查询数据库显示订单信息
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Check Out</title>
    <link rel="icon" type="image/png" href="..\..\assets\img\temp-logo.png">
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