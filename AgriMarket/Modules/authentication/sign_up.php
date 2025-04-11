<?php
require_once '../../includes/database.php';

$db = new Database();
$userClass = new User($db);

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone_number = trim($_POST['phone_number']);
    $home_address = trim($_POST['home_address']);
    $profile_image = $_FILES['profile_image'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = hash('sha256', $password);

        $upload_dir = __DIR__ . "/../../Assets/img/profile_img/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); 
        }

        $image_name = basename($profile_image['name']);
        $target_file = $upload_dir . $image_name;
        $image_path = "Assets/img/profile_img/" . $image_name;

        if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
            // Insert user into the database
            $result = $userClass->insertUser($first_name, $last_name, $username, $email, $hashed_password, 'Customer', $phone_number, $home_address, $image_path);

            if ($result) {
                $success = "Account created successfully. You can now <a href='login.php'>log in</a>.";
            } else {
                $error = "The username, email, or phone number is already registered.";
            }
        } else {
            $error = "Failed to upload profile image.";
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
    <title>AgriMarket - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/sign_up.css">
</head>

<body class="sign-up">
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

            <!-- Sign Up Form -->
            <form action="sign_up.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label for="home_address" class="form-label">Home Address</label>
                    <textarea class="form-control" id="home_address" name="home_address" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                </div>
                <hr>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>

            <div class="mt-3 text-center">
                <p>
                    Already have an account?
                    <a href="login.php">Log in here</a>.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>