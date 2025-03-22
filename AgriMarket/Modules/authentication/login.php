<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['role'])) {
        $_SESSION['role'] = $_POST['role'];

        switch ($_POST['role']) {
            case 'Customer':
                header("Location: ../customer/main_page.php");
                exit();
            case 'Vendor':
                header("Location: ../vendor/product_listings.php");
                exit();
            case 'Staff':
                header("Location: ../staff/staff_dashboard.php");
                exit();
            case 'Admin':
                header("Location: ../admin/admin_dashboard.php");
                exit();
            default:
                header("Location: login.php");
                exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AgriMarket - Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/login.css">
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center" style="height:100vh;">
    <div class="main-container text-center">
        <h1 class="mb-4">Testing</h1>

        <form method="POST" action="">
            <button type="submit" name="role" value="Customer" class="btn btn-primary m-2">
                Customer
            </button>
            <button type="submit" name="role" value="Vendor" class="btn btn-success m-2">
                Vendor
            </button>
            <button type="submit" name="role" value="Staff" class="btn btn-warning m-2">
                Staff
            </button>
            <button type="submit" name="role" value="Admin" class="btn btn-danger m-2">
                Admin
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>