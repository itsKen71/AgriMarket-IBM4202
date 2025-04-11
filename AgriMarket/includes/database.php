<?php

class Database
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "agrimarket";
    public $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function close()
    {
        $this->conn->close();
    }
}


class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function authenticateUser($username_email, $password)
    {

        $password_hashed = hash('sha256', $password);

        $stmt = $this->conn->prepare("SELECT user_id, role, password FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username_email, $username_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ($row['password'] === $password_hashed) {
                // Update the last_online field
                $update_stmt = $this->conn->prepare("UPDATE user SET last_online = NOW() WHERE user_id = ?");
                $update_stmt->bind_param("i", $row['user_id']);
                $update_stmt->execute();

                return [
                    'user_id' => $row['user_id'],
                    'role' => $row['role']
                ];
            }
        }

        return null;
    }

    function insertUser($first_name, $last_name, $username, $email, $password, $role, $phone_number, $home_address, $user_image)
    {

        $stmt = $this->conn->prepare("INSERT INTO user (first_name, last_name, username, user_image, email, password, role, phone_number, home_address, last_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $first_name, $last_name, $username, $user_image, $email, $password, $role, $phone_number, $home_address);

        return $stmt->execute();
    }

    function getUsernameFromUserID($user_id)
    {

        $stmt = $this->conn->prepare("SELECT username FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['username'];
        }

        return null;
    }

    function getEmailByUsername($username)
    {

        $stmt = $this->conn->prepare("SELECT email FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['email'];
        }

        return null;
    }

    function updatePasswordByUsername($username, $hashed_password)
    {

        $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);

        return $stmt->execute();
    }

    function getUserImageFromUserID($user_id)
    {

        $stmt = $this->conn->prepare("SELECT user_image FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['user_image'];
        }

        return "../../Assets/svg/person-circle.svg"; // Default image if not found
    }

    function updateUserDetails($email, $phone_number, $user_id)
    {
        $query = "UPDATE user SET email = ?, phone_number = ? WHERE user_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ssi", $email, $phone_number, $user_id);
            return $stmt->execute(); // Return true if success, false if failure
        }
        return false;
    }

    function getVendorIdByUserId($user_id)
    {
        $vendor_id = null;

        $stmt = $this->conn->prepare("SELECT vendor_id FROM vendor WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($vendor_id);
            $stmt->fetch();
            $stmt->close();
        }

        return $vendor_id;
    }

    function getActiveUser()
    {
        $oneMonthAgo = date("Y-m-d H:i:s", strtotime("-1 month"));

        // Get Active Customers
        $sqlCustomers = "SELECT COUNT(*) AS totalCustomers FROM user WHERE role='Customer' AND last_online >= '$oneMonthAgo'";

        // Get Active Vendors
        $sqlVendors = "SELECT COUNT(*) AS totalVendors FROM user WHERE role='Vendor' AND last_online >= '$oneMonthAgo'";

        $resultCustomers = mysqli_query($this->conn, $sqlCustomers);
        $resultVendors = mysqli_query($this->conn, $sqlVendors);

        return [
            "activeCustomers" => mysqli_fetch_assoc($resultCustomers)['totalCustomers'],
            "activeVendors" => mysqli_fetch_assoc($resultVendors)['totalVendors']
        ];
    }

    function getRole($user_id)
    {
        $stmt = $this->conn->prepare("SELECT role FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['role'] ?? null;
    }
}

class Customer
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }



    function updateCustomerInfo($user_id, $username, $first_name, $last_name, $email, $phone_number, $home_address, $image_path = null)
    {
        $query = "UPDATE user 
                  SET username = ?, 
                      first_name = ?, 
                      last_name = ?, 
                      email = ?, 
                      phone_number = ?, 
                      home_address = ?, 
                      user_image = IFNULL(?, user_image) 
                  WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssi", $username, $first_name, $last_name, $email, $phone_number, $home_address, $image_path, $user_id);
        return $stmt->execute();
    }

    function getOrderHistoryByUser($userId)
    {
        $sql = "
        SELECT o.order_id, o.order_date, o.price AS total_order_price,
            poh.product_id, poh.quantity, poh.sub_price, poh.status,
            p.product_name, p.unit_price, p.product_image, p.stock_quantity, p.description, p.weight,
            coh.status AS order_status,
            s.tracking_number,
            pym.payment_id, pym.payment_status,
            c.category_name,
            r.refund_id, r.refund_status
        FROM orders o
        INNER JOIN product_order poh ON o.order_id = poh.order_id
        INNER JOIN product p ON poh.product_id = p.product_id
        INNER JOIN customer_order_history coh ON o.order_id = coh.order_id
        INNER JOIN shipment s ON o.order_id = s.order_id
        INNER JOIN payment pym ON o.order_id = pym.order_id
        INNER JOIN category c ON c.category_id = p.category_id
        LEFT JOIN refund r 
        ON r.product_id = poh.product_id 
        AND r.order_id = o.order_id 
        AND r.user_id = o.user_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC, o.order_id DESC, p.product_name ASC
    ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("SQL Error: " . $this->conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orderId = $row['order_id'];
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'payment_id' => $row['payment_id'],
                    'order_date' => $row['order_date'],
                    'total_order_price' => $row['total_order_price'],
                    'order_status' => $row['order_status'],
                    'products' => [],
                    'tracking_number' => $row['tracking_number'],
                    'payment_status' => $row['payment_status']
                ];
            }
            $orders[$orderId]['products'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'product_image' => $row['product_image'],
                'unit_price' => $row['unit_price'],
                'quantity' => $row['quantity'],
                'sub_price' => $row['sub_price'],
                'stock_quantity' => $row['stock_quantity'],
                'status' => $row['status'],
                'description' => $row['description'],
                'weight' => $row['weight'],
                'category_name' => $row['category_name'],
                'refund_id' => $row['refund_id'],
                'refund_status' => $row['refund_status']
            ];
        }
        return $orders;
    }

    function getCustomerDetails($user_id)
    {
        $stmt = $this->conn->prepare("SELECT username, first_name, last_name, email, phone_number, home_address FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function updateCustomerProfile($user_id, $username, $first_name, $last_name, $email, $phone_number, $home_address)
    {
        $stmt = $this->conn->prepare("UPDATE user SET username = ?, first_name = ?, last_name = ?, email = ?, phone_number = ?, home_address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $phone_number, $home_address, $user_id);
        return $stmt->execute();
    }

    function upgradeToVendor($user_id, $plan_id, $end_date)
    { // Upgrade user to vendor and insert into vendor table
        // Upgrade user role
        $update_user = "UPDATE user SET role = 'Vendor' WHERE user_id = ?";
        $stmt = $this->conn->prepare($update_user);
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute())
            return false;

        // Insert into vendor table
        $insert_vendor = "INSERT INTO vendor (user_id, subscription_id, store_name, subscription_start_date, subscription_end_date)
                          VALUES (?, ?, 'New Store', CURDATE(), ?)";
        $stmt = $this->conn->prepare($insert_vendor);
        $stmt->bind_param("iis", $user_id, $plan_id, $end_date);
        return $stmt->execute();
    }

    function getCustomerEmails()
    {

        $sql = "SELECT first_name, last_name, email FROM user WHERE role = 'Customer' ";
        $result = mysqli_query($this->conn, $sql);

        $customers = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $fullName = $row['first_name'] . ' ' . $row['last_name'];

                $customers[] = [
                    'email' => $row['email'],
                    'full_name' => $fullName
                ];
            }
        }
        return $customers;
    }

    function getShipmentStatus($user_id)
    {
        $data = [];

        if ($user_id == -1) {
            $sql = "SELECT s.status, COUNT(s.shipping_id) AS totalShipments
                FROM shipment s
                JOIN orders o ON s.order_id = o.order_id
                GROUP BY s.status
                ORDER BY totalShipments DESC";
            $stmt = mysqli_prepare($this->conn, $sql);

        } else {

            $sql = "SELECT s.status, COUNT(DISTINCT s.shipping_id) AS totalShipments
                 FROM shipment s
                 JOIN orders o ON s.order_id = o.order_id
                 JOIN product_order po ON o.order_id = po.order_id
                 JOIN product p ON po.product_id = p.product_id
                 WHERE p.vendor_id = ?
                 GROUP BY s.status
                 ORDER BY totalShipments DESC";
            $stmt = mysqli_prepare($this->conn, $sql);

            mysqli_stmt_bind_param($stmt, "i", $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        $totalShipments = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
            $totalShipments += $row['totalShipments'];
        }

        foreach ($rows as $row) {
            $percentage = ($totalShipments > 0) ? ($row['totalShipments'] / $totalShipments) * 100 : 0;
            $data[] = ["label" => $row['status'], "y" => round($percentage, 2)];
        }

        return $data;
    }
}

class Vendor
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function isVendorTierThree($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT s.plan_name 
            FROM vendor v
            JOIN subscription s ON v.subscription_id = s.subscription_id
            WHERE v.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['plan_name'] === 'Tier_III';
        }

        return false;
    }

    function getVendorDetailsById($vendor_id)
    {
        $query = "SELECT v.store_name, u.first_name, u.last_name, u.email, u.phone_number 
                  FROM vendor v 
                  JOIN user u ON v.user_id = u.user_id 
                  WHERE v.vendor_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            error_log("No vendor found for vendor_id: " . $vendor_id); // Log the issue
        }

        return $result->fetch_assoc();
    }

    function getPlanName($plan_id)
    { // Get subscription plan name
        $query = "SELECT plan_name FROM subscription WHERE subscription_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result->fetch_assoc();
        return $plan ? $plan['plan_name'] : null;
    }

    function updateVendorSubscription($user_id, $plan_id, $end_date)
    { // Update vendor's subscription
        $update_vendor = "UPDATE vendor SET subscription_id = ?, subscription_start_date = CURDATE(), subscription_end_date = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($update_vendor);
        $stmt->bind_param("isi", $plan_id, $end_date, $user_id);
        return $stmt->execute();
    }

    function checkIfVendor($user_id)
    { // Check if user is already a vendor
        $query = "SELECT vendor_id FROM vendor WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    function getVendorList()
    {

        $sql = "SELECT v.vendor_id, v.store_name, s.plan_name, v.subscription_end_date, 
                    IFNULL(CONCAT(u.first_name, ' ', u.last_name), '-') AS staff_assistance, 
                    v.assist_by
                FROM vendor v
                LEFT JOIN user u ON u.user_id = v.assist_by
                JOIN subscription s ON v.subscription_id = s.subscription_id";

        $result = $this->conn->query($sql);

        return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function updateVendorAssistance($vendorId, $staffId)
    {

        $sql = "UPDATE vendor SET assist_by = ? WHERE vendor_id = ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ii", $staffId, $vendorId);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function getVendorAssisstanceList($user_id)
    {

        $sql = "SELECT v.store_name, r.request_description, r.request_type, r.request_date, r.request_id
                FROM request r 
                JOIN vendor v ON r.vendor_id = v.vendor_id
                WHERE v.assist_by = ? AND r.is_completed = FALSE
                ORDER BY r.request_date ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function updateVendorProfile($store_name, $vendor_id)
    {
        $query = "UPDATE vendor SET store_name = ? WHERE vendor_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("si", $store_name, $vendor_id);
            return $stmt->execute(); // Return true if success, false if failure
        }
        return false;
    }

    function isVendor($user_id)
    {
        $stmt = $this->conn->prepare("SELECT role FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user['role'] === 'Vendor';
    }

    function insertRequest($vendor_id, $request_type, $request_description)
    {
        $query = "INSERT INTO request (vendor_id, request_type, request_description, request_date) 
              VALUES (?, ?, ?, NOW())";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("iss", $vendor_id, $request_type, $request_description);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function getVendorDetails($user_id)
    {
        $query = "
        SELECT v.vendor_id, v.store_name, v.subscription_id, v.subscription_start_date, v.subscription_end_date, v.assist_by,
               u.user_id, u.email, u.phone_number, 
               s.plan_name, s.has_staff_support, s.upload_limit, s.has_low_stock_alert
        FROM vendor v
        JOIN user u ON v.user_id = u.user_id
        LEFT JOIN subscription s ON v.subscription_id = s.subscription_id
        WHERE v.user_id = ?
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function getSubscription()
    {
        $sql = "SELECT s.plan_name, COUNT(v.vendor_id) AS totalUsers 
                FROM vendor v JOIN subscription s 
                ON v.subscription_id = s.subscription_id
                GROUP BY s.plan_name
                ORDER BY totalUsers DESC";

        $result = mysqli_query($this->conn, $sql);
        $totalUsers = 0;
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $totalUsers += $row['totalUsers'];
        }

        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            $percentage = ($totalUsers > 0) ? ($row['totalUsers'] / $totalUsers) * 100 : 0;
            $data[] = ["label" => $row['plan_name'], "y" => round($percentage, 2)];
        }

        return $data;
    }

    function getUserIdByVendorId($vendor_id)
    {
        $query = "SELECT user_id FROM vendor WHERE vendor_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['user_id'];
        }

        return null; // Return null if no user_id is found
    }

    function displayStarsHere($rating)
    {
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
        $output = '';
        // Full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $output .= '<i class="fas fa-star text-warning"></i>';
        }
        // Half star
        if ($hasHalfStar) {
            $output .= '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        // Empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            $output .= '<i class="far fa-star text-warning"></i>';
        }
        return $output;
    }

}

class Staff
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function deleteComment($review_id)
    {

        $stmt = $this->conn->prepare("DELETE FROM review WHERE review_id = ?");
        $stmt->bind_param("i", $review_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function update_Promotion_Discount($discountCode, $promotionTitle, $promotionMessage, $startDate, $endDate, $discountPercentage, $minPurchaseAmount, $isActive, $created_by)
    {
        //Insert into Discount Table
        $sqlDiscount = "INSERT INTO discount (discount_code, discount_percentage,min_amount_purchase) VALUES (?,?,?)";

        $stmtDiscount = $this->conn->prepare($sqlDiscount);
        $stmtDiscount->bind_param("sdd", $discountCode, $discountPercentage, $minPurchaseAmount);

        if ($stmtDiscount->execute()) {
            //Get Last Row Discount ID
            $discount_id = $stmtDiscount->insert_id;

            //Insert into Promotion table
            $sqlPromotion = "INSERT INTO promotion(discount_id,promotion_title,promotion_message,promotion_start_date, promotion_end_date, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmtPromotion = $this->conn->prepare($sqlPromotion);
            $stmtPromotion->bind_param("issssii", $discount_id, $promotionTitle, $promotionMessage, $startDate, $endDate, $isActive, $created_by);

            if ($stmtPromotion->execute()) {
                return $stmtPromotion->insert_id;
            } else {
                return false;
            }
        } else {
            //Discount Record Insert Failed
            return false;
        }
    }

    function updateAssisstanceRequestStatus($request_id, $status)
    {
        $sql = "UPDATE request SET is_completed=? WHERE request_id= ?";
        $stmt = $this->conn->prepare($sql);

        //Convert Boolean to Integer 
        $status = (int) $status;

        $stmt->bind_param("ii", $status, $request_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }


    function getStaffList()
    {
        $sql = "SELECT 
                u.user_id,
                CONCAT(u.first_name, ' ', u.last_name) AS Name, 
                u.last_online, 
                COUNT(r.request_id) AS totalRequest, 
                SUM(CASE WHEN r.is_completed = 1 THEN 1 ELSE 0 END) AS totalCompleted,
                COALESCE((SUM(CASE WHEN r.is_completed = 1 THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(r.request_id), 0)), 0) AS progress_percentage,
                u.phone_number,
                u.home_address
                FROM user u
                LEFT JOIN vendor v ON u.user_id = v.user_id
                LEFT JOIN request r ON v.vendor_id = r.vendor_id
                WHERE u.role = 'Staff'
                GROUP BY u.user_id
                ORDER BY u.last_online DESC";

        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    function getPendingRequestList()
    {
        $sql = "SELECT 
            v.store_name, 
            c.category_name, 
            p.product_name, 
            p.description, 
            p.stock_quantity, 
            p.weight, 
            p.unit_price, 
            p.product_id, 
            p.product_image
        FROM vendor v 
        JOIN product p ON v.vendor_id = p.vendor_id
        LEFT JOIN category c ON p.category_id = c.category_id
        WHERE p.product_status = 'Pending'";

        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    function updatePendingRequestStatus($product_id, $status)
    {

        $sql = "UPDATE product SET product_status=? WHERE product_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $product_id);

        return $stmt->execute();
    }

}

class Admin
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function getAdminList()
    {

        $sql = "SELECT CONCAT(first_name , ' ' , last_name) AS Name, phone_number, home_address, last_online FROM user 
            WHERE role='Admin'";

        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    function getTopVendor()
    {
        $sql = "SELECT v.store_name AS label, SUM(po.sub_price) AS amountPurchase
            FROM product_order po
            JOIN product p ON po.product_id = p.product_id
            JOIN vendor v ON p.vendor_id = v.vendor_id
            GROUP BY v.vendor_id
            ORDER BY amountPurchase DESC
            LIMIT 5";

        $result = mysqli_query($this->conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                "label" => $row['label'],
                "y" => (float) $row['amountPurchase']
            ];
        }
        return $data;
    }


}

class Product
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function getShipmentDetails($tracking_number, $user_id)
    {
        $stmt = $this->conn->prepare("
        SELECT * 
        FROM shipment 
        WHERE tracking_number = ? 
        AND order_id IN (SELECT order_id FROM orders WHERE user_id = ?)
    ");
        $stmt->bind_param("si", $tracking_number, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function updateShipmentStatus($shipping_id, $new_status)
    {
        $stmt = $this->conn->prepare("UPDATE shipment SET status = ? WHERE shipping_id = ?");
        $stmt->bind_param("si", $new_status, $shipping_id);
        return $stmt->execute();
    }

    function getCategories()
    {

        $sql = "SELECT * FROM category ORDER BY category_id";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    function getApprovedProducts($category_id = null, $search_query = '', $filter = '')
    {

        $sql = "SELECT * FROM product WHERE product_status='Approved'";
        $params = [];
        $types = "";

        if ($category_id !== 'all') {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if (!empty($search_query)) {
            $sql .= " AND (product_name LIKE ? OR description LIKE ?)";
            $search_term = '%' . $search_query . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }

        switch ($filter) {
            case 'price_asc':
                $sql .= " ORDER BY unit_price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY unit_price DESC";
                break;
            case 'stock_asc':
                $sql .= " ORDER BY stock_quantity ASC";
                break;
            case 'stock_desc':
                $sql .= " ORDER BY stock_quantity DESC";
                break;
            case 'sold_asc':
                $sql .= " ORDER BY sold_quantity ASC";
                break;
            case 'sold_desc':
                $sql .= " ORDER BY sold_quantity DESC";
                break;
            case 'weight_asc':
                $sql .= " ORDER BY weight ASC";
                break;
            case 'weight_desc':
                $sql .= " ORDER BY weight DESC";
                break;
            case 'recent':
                $sql .= " ORDER BY product_id DESC";
                break;
            default:
                $sql .= " ORDER BY RAND()";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }

    function getVendorProducts($vendor_id, $search_query = '', $filter = '')
    {
        $sql = "SELECT * FROM product WHERE vendor_id = ? AND product_status = 'Approved'";
        $params = [$vendor_id];
        $types = "i";

        if (!empty($search_query)) {
            $sql .= " AND (product_name LIKE ? OR description LIKE ?)";
            $search_term = '%' . $search_query . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }

        // Apply filters
        switch ($filter) {
            case 'price_asc':
                $sql .= " ORDER BY unit_price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY unit_price DESC";
                break;
            case 'stock_asc':
                $sql .= " ORDER BY stock_quantity ASC";
                break;
            case 'stock_desc':
                $sql .= " ORDER BY stock_quantity DESC";
                break;
            case 'sold_asc':
                $sql .= " ORDER BY sold_quantity ASC";
                break;
            case 'sold_desc':
                $sql .= " ORDER BY sold_quantity DESC";
                break;
            case 'weight_asc':
                $sql .= " ORDER BY weight ASC";
                break;
            case 'weight_desc':
                $sql .= " ORDER BY weight DESC";
                break;
            case 'recent':
                $sql .= " ORDER BY product_id DESC";
                break;
            default:
                $sql .= " ORDER BY RAND()";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function insertProduct($vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status)
    {
        $query = "INSERT INTO product (vendor_id, category_id, product_name, product_image, description, stock_quantity, weight, unit_price, product_status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("iisssidds", $vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status);
            return $stmt->execute(); // Return true if successful, false if failed
        }
        return false;
    }

    function uploadProductImage($file, $upload_dir = "../Assets/img/product_img/")
    {
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $image_name = basename($file["name"]);
        $target_file = $upload_dir . $image_name;
        $image_path = "Assets/img/product_img/" . $image_name;

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $image_path;
        }
        throw new Exception("Error uploading image.");
    }

    function updateProduct($product_id, $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status)
    {
        $query = "UPDATE product 
              SET category_id = ?, product_image = ?, description = ?, stock_quantity = ?, weight = ?, unit_price = ? , product_status = ? 
              WHERE product_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("issiddsi", $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status, $product_id);
            return $stmt->execute(); // Return true if successful, false if failed
        }
        return false;
    }

    function updateProductImage($file, $current_image, $upload_dir = "../Assets/img/product_img/")
    {
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $image_path = $current_image; // Default to current image if no new image is uploaded

        if (!empty($file["name"])) {
            $image_name = basename($file["name"]);
            $target_file = $upload_dir . $image_name;
            $image_path = "Assets/img/product_img/" . $image_name;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                if (file_exists($current_image)) {
                    unlink($current_image); // Remove the old image if a new image is uploaded
                }
            } else {
                throw new Exception("Error uploading image.");
            }
        }
        return $image_path;
    }

    function getPendingProductCount($vendor_id)
    { // Used to determine whether it exceed upload_limit
        $query = "SELECT COUNT(*) AS pending_count FROM product WHERE vendor_id = ? AND product_status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['pending_count'] ?? 0; // Return 0 if no result
    }

    function getLowStockProducts($vendor_id, $threshold = 10)
    {
        $query = "
        SELECT product_id, product_name, stock_quantity 
        FROM product 
        WHERE vendor_id = ? AND stock_quantity < ? AND product_status = 'Approved'
        ORDER BY stock_quantity ASC
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $vendor_id, $threshold);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function getProductsByStatus($vendor_id, $status)
    {
        $sql = "
        SELECT p.product_id, p.product_name, p.description, p.stock_quantity, p.weight, p.unit_price, p.product_status, p.product_image,
        p.product_status,
        c.category_name
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        WHERE p.vendor_id = ? AND p.product_status = ?
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $vendor_id, $status);
        $stmt->execute();
        return $stmt->get_result();
    }

    function getTopProduct($user_id)
    {
        $topProducts = [];
        $currentYear = date("Y");

        if ($user_id == -1) {
            // Admin view 
            $sql = "SELECT p.product_image, p.product_name, SUM(po.quantity) AS total_quantity
                FROM product_order po
                JOIN orders o ON po.order_id = o.order_id
                JOIN product p ON po.product_id = p.product_id
                WHERE YEAR(o.order_date) = ?
                GROUP BY p.product_id
                ORDER BY total_quantity DESC
                LIMIT 5";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $currentYear);
        } else {
            // Vendor view 
            $sql = "SELECT p.product_image, p.product_name, SUM(po.quantity) AS total_quantity
                FROM product_order po
                JOIN orders o ON po.order_id = o.order_id
                JOIN product p ON po.product_id = p.product_id
                JOIN vendor v ON p.vendor_id = v.vendor_id
                WHERE YEAR(o.order_date) = ? AND v.vendor_id = ?
                GROUP BY p.product_id
                ORDER BY total_quantity DESC
                LIMIT 5";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $currentYear, $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $topProducts[] = $row;
        }

        return $topProducts;
    }

    function getNumberProducts($user_id)
    {
        $sql = "SELECT COUNT(product.product_id) AS totalProducts
                FROM product
                WHERE product.vendor_id = '$user_id'";

        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);

        return [
            "total_product" => $row['totalProducts']
        ];
    }

}

class Payment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    function getRefundList()
    {
        $sql = "SELECT  r.refund_id,r.order_id, p.product_name, r.refund_amount, r.refund_date ,r.reason
                FROM product p
                JOIN refund r ON p.product_id = r.product_id
                JOIN user u ON r.user_id = u.user_id
                WHERE r.refund_status='Pending'
                ORDER BY r.refund_date ASC";

        $result = $this->conn->query($sql);

        return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    function updateRefund($request_id, $status, $current_date, $user_id)
    {
        //Update refund status
        $sql = "UPDATE refund SET refund_status=?, refund_approve_date=?, approve_by=? WHERE refund_id= ?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ssii", $status, $current_date, $user_id, $request_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function getRefundPercentage($vendor_id)
    {
        $currentYear = date("Y");

        if ($vendor_id == -1) {
            $sqlRefunds = "
                SELECT COUNT(*) AS totalRefunds
                FROM refund
                WHERE YEAR(refund_date) = '$currentYear'
            ";

            $sqlOrders = "
                SELECT COUNT(*) AS totalOrders
                FROM orders
                WHERE YEAR(order_date) = '$currentYear'
            ";
        } else {
            $sqlRefunds = "
                SELECT COUNT(*) AS totalRefunds
                FROM refund
                JOIN product ON refund.product_id = product.product_id
                WHERE YEAR(refund.refund_date) = '$currentYear'
                AND product.vendor_id = '$vendor_id'
            ";

            $sqlOrders = "
                SELECT COUNT(DISTINCT orders.order_id) AS totalOrders
                FROM orders
                JOIN product_order ON orders.order_id = product_order.order_id
                JOIN product ON product_order.product_id = product.product_id
                WHERE YEAR(orders.order_date) = '$currentYear'
                AND product.vendor_id = '$vendor_id'
            ";
        }

        $resultRefunds = mysqli_query($this->conn, $sqlRefunds);
        $resultOrders = mysqli_query($this->conn, $sqlOrders);

        $totalRefunds = mysqli_fetch_assoc($resultRefunds)['totalRefunds'] ?? 0;
        $totalOrders = mysqli_fetch_assoc($resultOrders)['totalOrders'] ?? 0;

        $refundPercentage = ($totalOrders > 0) ? ($totalRefunds / $totalOrders) * 100 : 0;

        return ["totalRefundPercentage" => round($refundPercentage, 2)];
    }

    function getRevenue($user_id, $option)
    {
        $currentYear = date("Y");

        $select = "";
        $groupBy = "";
        $orderBy = "";
        $labels = [];
        $data = [];

        switch ($option) {
            case 'quarterly':
                $select = "QUARTER(order_date) AS duration";
                $groupBy = "QUARTER(order_date)";
                $orderBy = "QUARTER(order_date)";
                $labels = [1 => "Q1", 2 => "Q2", 3 => "Q3", 4 => "Q4"];
                foreach ($labels as $q => $label) {
                    $data[$q] = ["duration" => $label, "revenue" => 0];
                }
                break;

            case 'yearly':
                $select = "YEAR(order_date) AS duration";
                $groupBy = "YEAR(order_date)";
                $orderBy = "YEAR(order_date)";
                $startYear = $currentYear - 4;
                for ($y = $startYear; $y <= $currentYear; $y++) {
                    $labels[$y] = (string) $y;
                    $data[$y] = ["duration" => (string) $y, "revenue" => 0];
                }
                break;

            case 'monthly':
            default:
                $select = "MONTH(order_date) AS duration";
                $groupBy = "MONTH(order_date)";
                $orderBy = "MONTH(order_date)";
                $labels = [
                    1 => "Jan",
                    2 => "Feb",
                    3 => "Mar",
                    4 => "Apr",
                    5 => "May",
                    6 => "Jun",
                    7 => "Jul",
                    8 => "Aug",
                    9 => "Sep",
                    10 => "Oct",
                    11 => "Nov",
                    12 => "Dec"
                ];
                foreach ($labels as $m => $label) {
                    $data[$m] = ["duration" => $label, "revenue" => 0];
                }
                break;
        }

        // Check whether it is admin or vendor
        if ($user_id == -1) {
            // Admin
            $sql = "SELECT $select, SUM(price) AS revenue
                    FROM orders
                    WHERE YEAR(order_date) = ?
                    GROUP BY $groupBy
                    ORDER BY $orderBy";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $currentYear);
        } else {
            // Vendor
            $sql = "SELECT $select, SUM(price) AS revenue
                    FROM orders
                    JOIN product_order po ON orders.order_id = po.order_id
                    JOIN product p ON po.product_id = p.product_id
                    WHERE YEAR(order_date) = ? AND p.vendor_id = ?
                    GROUP BY $groupBy
                    ORDER BY $orderBy";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $currentYear, $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row['duration'];
            $label = $labels[$key] ?? "Unknown";
            $data[$key] = ["duration" => $label, "revenue" => (float) $row['revenue']];
        }

        return array_values($data);
    }

    function topPaymentMethod($user_id)
    {
        $currentYear = date("Y");

        // Admin sees all data
        if ($user_id == -1) {
            $sql = "SELECT payment.payment_method, COUNT(*) AS frequency
                FROM payment 
                JOIN orders ON payment.order_id = orders.order_id
                WHERE YEAR(payment.transaction_date) = ?
                GROUP BY payment.payment_method
                ORDER BY frequency DESC";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $currentYear);
        } else {
            // Vendor view 
            $sql = "SELECT payment.payment_method, COUNT(DISTINCT payment.payment_id) AS frequency
                FROM payment 
                JOIN orders ON payment.order_id = orders.order_id
                JOIN product_order po ON orders.order_id = po.order_id
                JOIN product p ON po.product_id = p.product_id
                WHERE YEAR(payment.transaction_date) = ? AND p.vendor_id = ?
                GROUP BY payment.payment_method
                ORDER BY frequency DESC";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $currentYear, $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        $totalFrequency = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
            $totalFrequency += $row['frequency'];
        }

        $data = [];
        foreach ($rows as $row) {
            $percentage = ($totalFrequency > 0) ? ($row['frequency'] / $totalFrequency) * 100 : 0;
            $data[] = [
                "label" => $row['payment_method'],
                "y" => round($percentage, 2)
            ];
        }

        return $data;
    }

    function getOrders($user_id, $option)
    {
        $currentYear = date("Y");

        $select = "";
        $groupBy = "";
        $orderBy = "";
        $labels = [];
        $data = [];

        switch ($option) {
            case 'quarterly':
                $select = "QUARTER(o.order_date) AS duration";
                $groupBy = "QUARTER(o.order_date)";
                $orderBy = "QUARTER(o.order_date)";
                $labels = [1 => "Q1", 2 => "Q2", 3 => "Q3", 4 => "Q4"];
                foreach ($labels as $q => $label) {
                    $data[$q] = ["duration" => $label, "total_orders" => 0];
                }
                break;

            case 'yearly':
                $select = "YEAR(o.order_date) AS duration";
                $groupBy = "YEAR(o.order_date)";
                $orderBy = "YEAR(o.order_date)";
                $startYear = $currentYear - 4;
                for ($y = $startYear; $y <= $currentYear; $y++) {
                    $labels[$y] = (string) $y;
                    $data[$y] = ["duration" => (string) $y, "total_orders" => 0];
                }
                break;

            case 'monthly':
            default:
                $select = "MONTH(o.order_date) AS duration";
                $groupBy = "MONTH(o.order_date)";
                $orderBy = "MONTH(o.order_date)";
                $labels = [
                    1 => "Jan",
                    2 => "Feb",
                    3 => "Mar",
                    4 => "Apr",
                    5 => "May",
                    6 => "Jun",
                    7 => "Jul",
                    8 => "Aug",
                    9 => "Sep",
                    10 => "Oct",
                    11 => "Nov",
                    12 => "Dec"
                ];
                foreach ($labels as $m => $label) {
                    $data[$m] = ["duration" => $label, "total_orders" => 0];
                }
                break;
        }

        if ($user_id == -1) {
            // Admin
            $sql = "SELECT $select, COUNT(DISTINCT o.order_id) AS total_orders
                    FROM orders o
                    WHERE YEAR(o.order_date) = ?
                    GROUP BY $groupBy
                    ORDER BY $orderBy";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $currentYear);
        } else {
            // Vendor
            $sql = "SELECT $select, COUNT(DISTINCT o.order_id) AS total_orders
                    FROM orders o
                    JOIN product_order po ON o.order_id = po.order_id
                    JOIN product p ON po.product_id = p.product_id
                    WHERE YEAR(o.order_date) = ? AND p.vendor_id = ?
                    GROUP BY $groupBy
                    ORDER BY $orderBy";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $currentYear, $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row['duration'];
            $label = $labels[$key] ?? "Unknown";
            $data[$key] = [
                "duration" => $label,
                "total_orders" => (int) $row['total_orders']
            ];
        }

        return array_values($data);
    }
}