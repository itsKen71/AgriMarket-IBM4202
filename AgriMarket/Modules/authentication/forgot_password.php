<?php
require_once '../../includes/database.php';
require '../../includes/PHPMailer/src/PHPMailer.php';
require '../../includes/PHPMailer/src/SMTP.php';
require '../../includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Initialize database connection and user class
$db = new Database();
$userClass = new User($db);

// Initialize variables for error, success messages, and step tracking
$error = "";
$success = "";
$step = isset($_SESSION['step']) ? $_SESSION['step'] : 1;

// Handle form submissions based on the current step
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Handle username submission and send OTP
    if (isset($_POST['username']) && $step === 1) {
        $username = trim($_POST['username']);
        $email = $userClass->getEmailByUsername($username);

        if ($email) {
            // Generate OTP and store it in the session
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            // Configure and send the OTP email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kenjichong88@gmail.com'; // Replace with your email
                $mail->Password = 'zqrg tdtj lxrv lgpk'; // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('agrimarketonline24@gmail.com', 'AgriMarket');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP - AgriMarket';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                        <div style='text-align: center; padding: 20px; background-color: #f5f1eb; border-bottom: 2px solid #c9d8b6;'>
                            <h2 style='color: #4CAF50; margin: 0;'>AgriMarket</h2>
                            <p style='margin: 0; font-size: 14px;'>Your trusted agricultural marketplace</p>
                        </div>
                        <div style='padding: 20px;'>
                            <p>Dear <b>{$_SESSION['username']}</b>,</p>
                            <p>We received a request to reset your password. Use the OTP below to reset your password:</p>
                            <div style='text-align: center; margin: 20px 0;'>
                                <span style='font-size: 24px; font-weight: bold; color: #4CAF50; padding: 10px 20px; border: 2px dashed #4CAF50; display: inline-block;'>
                                    $otp
                                </span>
                            </div>
                            <p>If you did not request a password reset, please ignore this email or contact our support team.</p>
                            <p>Thank you,<br>The AgriMarket Team</p>
                        </div>
                        <div style='text-align: center; padding: 10px; background-color: #f5f1eb; border-top: 2px solid #c9d8b6; font-size: 12px; color: #777;'>
                            <p style='margin: 0;'>© 2025 AgriMarket. All rights reserved.</p>
                        </div>
                    </div>
                ";

                $mail->send();
                $success = "OTP sent to your associated account.";
                $_SESSION['step'] = 2; // Move to step 2
                $step = 2;
            } catch (Exception $e) {
                $error = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Username not found.";
        }
    }
    // Step 2: Handle OTP verification
    elseif (isset($_POST['otp']) && $step === 2) {
        $otp = trim($_POST['otp']);
        if ($otp == $_SESSION['otp']) {
            $success = "OTP verified. You can now reset your password.";
            $_SESSION['step'] = 3; // Move to step 3
            $step = 3;
        } else {
            $error = "Invalid OTP. Please try again.";
            $step = 2;
        }
    }
    // Step 3: Handle password reset
    elseif (isset($_POST['password']) && isset($_POST['confirm_password']) && $step === 3) {
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if ($password === $confirm_password) {
            // Hash the new password and update it in the database
            $hashed_password = hash('sha256', $password);
            $username = $_SESSION['username'];

            if ($userClass->updatePasswordByUsername($username, $hashed_password)) {
                $success = "Password reset successfully. You can now <a href='login.php'>log in</a>.";
                // Clear session data related to the password reset process
                unset($_SESSION['otp'], $_SESSION['username'], $_SESSION['email'], $_SESSION['step']);
                $step = 1; // Reset to step 1
            } else {
                $error = "Failed to reset password. Please try again.";
                $step = 3;
            }
        } else {
            $error = "Passwords do not match.";
            $step = 3;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../assets/img/logo.png">
    <title>AgriMarket - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/forgot_password.css">
</head>

<body class="forgot-password">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-center bg-light">
            <img src="../../assets/img/logo.png" alt="logo" class="logo me-2" style="height:40px;">
            <span class="fs-4">AgriMarket</span>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Step 1: Enter Username -->
            <?php if ($step === 1): ?>
                <form action="forgot_password.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Enter your username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                </form>
            <?php endif; ?>

            <!-- Step 2: Enter OTP -->
            <?php if ($step === 2): ?>
                <form action="forgot_password.php" method="POST">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Enter OTP</label>
                        <input type="text" class="form-control" id="otp" name="otp" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                </form>
            <?php endif; ?>

            <!-- Step 3: Reset Password -->
            <?php if ($step === 3): ?>
                <form action="forgot_password.php" method="POST">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>