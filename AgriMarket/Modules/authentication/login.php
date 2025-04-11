<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/database.php';

$db = new Database();
$userClass = new User($db);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username']);
    $password = trim($_POST['password']);

    $user = $userClass->authenticateUser($username_email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
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
                $error = "Invalid user role.";
        }
    } else {
        $error = "Invalid username/email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <title>AgriMarket - Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/login.css">
</head>

<body class="login">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-center bg-light">
            <img src="../../assets/img/logo.png" alt="logo" class="logo me-2" style="height:40px;">
            <span class="fs-4">AgriMarket</span>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Login form -->
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <p>
                    Forgot your password?
                    <a href="forgot_password.php">Click here</a>.
                </p>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="mt-3 text-center">
                <p>
                    Don't have an account?
                    <a href="../authentication/sign_up.php">Sign up here</a>.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>