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
    private $user_id;
    private $username;
    private $first_name;
    private $last_name;
    private $email;
    private $password;
    private $role;
    private $phone_number;
    private $home_address;
    private $user_image;
    private $last_online;

    public function __construct($db, $user_id = null, $username = null, $first_name = null, $last_name = null, $email = null, $password = null, $role = null, $phone_number = null, $home_address = null, $user_image = null, $last_online = null)
    {
        $this->conn = $db->conn;
        $this->user_id = $user_id;
        $this->username = $username;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->phone_number = $phone_number;
        $this->home_address = $home_address;
        $this->user_image = $user_image;
        $this->last_online = $last_online;
    }

    // Getter methods
    public function getUserId()
    {
        return $this->user_id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return $this->role;
    }

    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    public function getHomeAddress()
    {
        return $this->home_address;
    }

    public function getUserImage()
    {
        return $this->user_image;
    }

    public function getLastOnline()
    {
        return $this->last_online;
    }

    // Setter methods
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
    }

    public function setHomeAddress($home_address)
    {
        $this->home_address = $home_address;
    }

    public function setUserImage($user_image)
    {
        $this->user_image = $user_image;
    }

    public function setLastOnline($last_online)
    {
        $this->last_online = $last_online;
    }

    // Authenticate user by username/email and password
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

    // Insert a new user into the database
    function insertUser()
    {
        $stmt = $this->conn->prepare("INSERT INTO user (first_name, last_name, username, user_image, email, password, role, phone_number, home_address, last_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param(
            "sssssssss",
            $this->first_name,
            $this->last_name,
            $this->username,
            $this->user_image,
            $this->email,
            $this->password,
            $this->role,
            $this->phone_number,
            $this->home_address
        );

        return $stmt->execute();
    }

    // Get username by user ID
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

    // Get email by username
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

    // Update password for a user by username
    function updatePasswordByUsername($username, $hashed_password)
    {

        $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);

        return $stmt->execute();
    }

    // Get user profile image by user ID
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

    // Update user details (email and phone number) by user ID
    function updateUserDetails($email, $phone_number, $user_id)
    {
        $query = "UPDATE user SET email = ?, phone_number = ? WHERE user_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ssi", $email, $phone_number, $user_id);
            return $stmt->execute(); // Return true if success, false if failure
        }
        return false;
    }

    // Get vendor ID by user ID
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

    // Get the count of active users (customers and vendors) in the last month
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

    // Get the role of a user by user ID
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

class Customer extends User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    // Update customer profile information
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

    // Get order history for a user
    function getOrderHistoryByUser($userId)
    {
        $sql = "
        SELECT o.order_id, o.order_date, o.price AS total_order_price,
            poh.product_id, poh.quantity, poh.sub_price, poh.status,
            p.product_name, p.unit_price, p.product_image, p.stock_quantity, p.description, p.weight,
            coh.status AS order_status,
            s.tracking_number, s.estimated_delivery_date,
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
                    'estimated_delivery_date' => $row['estimated_delivery_date'],
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

    // Get customer details by user ID
    function getCustomerDetails($user_id)
    {
        $stmt = $this->conn->prepare("SELECT username, first_name, last_name, email, phone_number, home_address FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update customer profile details
    function updateCustomerProfile($user_id, $username, $first_name, $last_name, $email, $phone_number, $home_address)
    {
        $stmt = $this->conn->prepare("UPDATE user SET username = ?, first_name = ?, last_name = ?, email = ?, phone_number = ?, home_address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $phone_number, $home_address, $user_id);
        return $stmt->execute();
    }

    // Upgrade a customer to a vendor
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

    // Get a list of customer emails
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

    // Get shipment status for a user or vendor
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

class Vendor extends Customer
{
    private $conn;
    private $vendor_id; // Unique ID for the vendor
    private $subscription_id; // Subscription plan ID
    private $store_name; // Vendor's store name
    private $subscription_start_date; // Start date of the subscription
    private $subscription_end_date; // End date of the subscription
    private $assist_by; // Staff assigned to assist the vendor

    public function __construct($db, $vendor_id = null, $subscription_id = null, $store_name = null, $subscription_start_date = null, $subscription_end_date = null, $assist_by = null)
    {
        parent::__construct($db); // Call the parent constructor to initialize inherited properties
        $this->conn = $db->conn;
        $this->vendor_id = $vendor_id;
        $this->subscription_id = $subscription_id;
        $this->store_name = $store_name;
        $this->subscription_start_date = $subscription_start_date;
        $this->subscription_end_date = $subscription_end_date;
        $this->assist_by = $assist_by;
    }

    // Getter methods
    public function getVendorId()
    {
        return $this->vendor_id;
    }

    public function getSubscriptionId()
    {
        return $this->subscription_id;
    }

    public function getStoreName()
    {
        return $this->store_name;
    }

    public function getSubscriptionStartDate()
    {
        return $this->subscription_start_date;
    }

    public function getSubscriptionEndDate()
    {
        return $this->subscription_end_date;
    }

    public function getAssistBy()
    {
        return $this->assist_by;
    }

    // Setter methods
    public function setVendorId($vendor_id)
    {
        $this->vendor_id = $vendor_id;
    }

    public function setSubscriptionId($subscription_id)
    {
        $this->subscription_id = $subscription_id;
    }

    public function setStoreName($store_name)
    {
        $this->store_name = $store_name;
    }

    public function setSubscriptionStartDate($subscription_start_date)
    {
        $this->subscription_start_date = $subscription_start_date;
    }

    public function setSubscriptionEndDate($subscription_end_date)
    {
        $this->subscription_end_date = $subscription_end_date;
    }

    public function setAssistBy($assist_by)
    {
        $this->assist_by = $assist_by;
    }

    // Check if a vendor has a Tier 3 subscription
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

    // Check if a vendor has a Tier 2 & 3 subscription
    function isVendorTierTwoOrThree($user_id)
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
            return in_array($row['plan_name'], ['Tier_II', 'Tier_III']);
        }

        return false;
    }


    // Get vendor details by vendor ID
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

    // Get subscription plan name by plan ID
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

    // Update vendor subscription details
    function updateVendorSubscription($user_id, $plan_id, $end_date)
    { // Update vendor's subscription
        $update_vendor = "UPDATE vendor SET subscription_id = ?, subscription_start_date = CURDATE(), subscription_end_date = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($update_vendor);
        $stmt->bind_param("isi", $plan_id, $end_date, $user_id);
        return $stmt->execute();
    }

    // Check if a user is already a vendor
    function checkIfVendor($user_id)
    { // Check if user is already a vendor
        $query = "SELECT vendor_id FROM vendor WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Get a list of all vendors
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

    // Update vendor assistance assignment
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

    // Get a list of vendor assistance requests
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

    // Update vendor profile details
    function updateVendorProfile($store_name, $vendor_id)
    {
        $query = "UPDATE vendor SET store_name = ? WHERE vendor_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("si", $store_name, $vendor_id);
            return $stmt->execute(); // Return true if success, false if failure
        }
        return false;
    }

    // Check if a user is a vendor
    function isVendor($user_id)
    {
        $stmt = $this->conn->prepare("SELECT role FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user['role'] === 'Vendor';
    }

    // Insert a new assistance request for a vendor
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

    // Get vendor details by user ID
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

    // Get subscription data for analytics
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

    // Get user ID associated with a vendor ID
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

    // Display star ratings for a vendor
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

class Staff extends User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    // Delete a review by review ID
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

    // Update promotion and discount details
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

    // Update the status of an assistance request
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

    // Get a list of all staff members
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
                LEFT JOIN vendor v ON u.user_id = v.assist_by
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

    // Get a list of pending product requests
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

    // Update the status of a pending product request
    function updatePendingRequestStatus($product_id, $status)
    {

        $sql = "UPDATE product SET product_status=? WHERE product_id=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $product_id);

        return $stmt->execute();
    }
}

class Admin extends User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->conn;
    }

    // Get a list of all admins
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

    // Get the top-performing vendors
    function getTopVendor()
    {
        $sql = "SELECT v.store_name AS label, SUM(po.quantity) AS amount
            FROM product_order po
            JOIN product p ON po.product_id = p.product_id
            JOIN vendor v ON p.vendor_id = v.vendor_id
            LEFT JOIN refund r ON r.product_id = po.product_id AND r.order_id = po.order_id
            WHERE (r.refund_status IS NULL OR r.refund_status != 'Approved')
            GROUP BY v.vendor_id
            ORDER BY amount DESC
            LIMIT 5";

        $result = mysqli_query($this->conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                "label" => $row['label'],
                "y" => (float) $row['amount']
            ];
        }
        return $data;
    }
}

class Product
{
    private $conn;
    private $product_id;
    private $vendor_id;
    private $category_id;
    private $product_name;
    private $product_image;
    private $description;
    private $stock_quantity;
    private $weight;
    private $unit_price;
    private $product_status;

    public function __construct($db, $product_id = null, $vendor_id = null, $category_id = null, $product_name = null, $product_image = null, $description = null, $stock_quantity = null, $weight = null, $unit_price = null, $product_status = null)
    {
        $this->conn = $db->conn;
        $this->product_id = $product_id;
        $this->vendor_id = $vendor_id;
        $this->category_id = $category_id;
        $this->product_name = $product_name;
        $this->product_image = $product_image;
        $this->description = $description;
        $this->stock_quantity = $stock_quantity;
        $this->weight = $weight;
        $this->unit_price = $unit_price;
        $this->product_status = $product_status;
    }

    // Getter methods
    public function getProductId()
    {
        return $this->product_id;
    }

    public function getVendorId()
    {
        return $this->vendor_id;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function getProductName()
    {
        return $this->product_name;
    }

    public function getProductImage()
    {
        return $this->product_image;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStockQuantity()
    {
        return $this->stock_quantity;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    public function getProductStatus()
    {
        return $this->product_status;
    }

    // Setter methods
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    public function setVendorId($vendor_id)
    {
        $this->vendor_id = $vendor_id;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
    }

    public function setProductName($product_name)
    {
        $this->product_name = $product_name;
    }

    public function setProductImage($product_image)
    {
        $this->product_image = $product_image;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setStockQuantity($stock_quantity)
    {
        $this->stock_quantity = $stock_quantity;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
    }

    public function setProductStatus($product_status)
    {
        $this->product_status = $product_status;
    }

    // Get shipment details by tracking number and user ID
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

    // Update the status of a shipment
    function updateShipmentStatus($shipping_id, $new_status)
    {
        $stmt = $this->conn->prepare("UPDATE shipment SET status = ? WHERE shipping_id = ?");
        $stmt->bind_param("si", $new_status, $shipping_id);
        return $stmt->execute();
    }

    // Get a list of all product categories
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

    // Get approved products to be displayed with optional filters
    function getApprovedProducts($category_id = null, $search_query = '', $filter = '', $preferred_terms = [])
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

        $order_by = [];
        $preferred_terms = array_slice($preferred_terms, 0, 5);

        if (!empty($preferred_terms)) {
            foreach ($preferred_terms as $term) {
                $clean_term = $this->conn->real_escape_string($term);
                $order_by[] = "(product_name LIKE '%$clean_term%' OR description LIKE '%$clean_term%') DESC";
            }
        }

        switch ($filter) {
            case 'price_asc':
                $order_by[] = "unit_price ASC";
                break;
            case 'price_desc':
                $order_by[] = "unit_price DESC";
                break;
            case 'stock_asc':
                $order_by[] = "stock_quantity ASC";
                break;
            case 'stock_desc':
                $order_by[] = "stock_quantity DESC";
                break;
            case 'sold_asc':
                $order_by[] = "sold_quantity ASC";
                break;
            case 'sold_desc':
                $order_by[] = "sold_quantity DESC";
                break;
            case 'weight_asc':
                $order_by[] = "weight ASC";
                break;
            case 'weight_desc':
                $order_by[] = "weight DESC";
                break;
            case 'recent':
                $order_by[] = "product_id DESC";
                break;
        }

        if (empty($filter)) {
            if (!empty($preferred_terms)) {
                $order_by[] = "RAND()";
            } else {
                $order_by[] = "RAND()";
            }
        }

        if (!empty($order_by)) {
            $sql .= " ORDER BY " . implode(", ", $order_by);
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get products for a vendor with optional filters
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

    // Insert a new product into the database
    public function insertProduct()
    {
        $query = "INSERT INTO product (vendor_id, category_id, product_name, product_image, description, stock_quantity, weight, unit_price, product_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iisssidds",
            $this->vendor_id,
            $this->category_id,
            $this->product_name,
            $this->product_image,
            $this->description,
            $this->stock_quantity,
            $this->weight,
            $this->unit_price,
            $this->product_status
        );
        return $stmt->execute();
    }

    // Upload a product image
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

    // Update product details
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

    // Update the product image
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

    // Get the count of pending products for a vendor
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

    // Get low stock products for a vendor
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

    // Get products by status for a vendor
    function getProductsByStatus($vendor_id, $status)
    {
        $sql = "
        SELECT p.product_id, p.product_name, p.description, p.stock_quantity, p.weight, p.unit_price, p.product_status, p.product_image,
        p.product_status,
        c.category_name
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        WHERE p.vendor_id = ? AND p.product_status = ?
        ORDER BY p.product_name ASC
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $vendor_id, $status);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get the top-selling products
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
                LEFT JOIN refund r
                    ON r.product_id= po.product_id
                    AND r.order_id = po.order_id
                    AND r.refund_status='Approved'
                WHERE YEAR(o.order_date) = ?
                   AND r.refund_id IS NULL
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
                LEFT JOIN refund r
                        ON r.product_id = po.product_id
                        AND r.order_id = po.order_id
                        AND r.refund_status = 'Approved'
                WHERE YEAR(o.order_date) = ? AND v.vendor_id = ? AND r.refund_id IS NULL
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

    // Get the total number of approved products for a vendor
    function getNumberProducts($user_id)
    {
        $sql = "SELECT COUNT(product.product_id) AS totalProducts
                FROM product
                WHERE product.vendor_id = '$user_id' AND product.product_status='Approved'";

        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);

        return [
            "total_product" => $row['totalProducts']
        ];
    }
}

class Payment extends Product
{
    private $conn;
    private $payment_id;
    private $order_id;
    private $user_id;
    private $total_amount;
    private $payment_method;
    private $payment_status;
    private $transaction_date;

    public function __construct($db, $payment_id = null, $order_id = null, $user_id = null, $total_amount = null, $payment_method = null, $payment_status = null, $transaction_date = null)
    {
        $this->conn = $db->conn;
        $this->payment_id = $payment_id;
        $this->order_id = $order_id;
        $this->user_id = $user_id;
        $this->total_amount = $total_amount;
        $this->payment_method = $payment_method;
        $this->payment_status = $payment_status;
        $this->transaction_date = $transaction_date;
    }

    // Getter methods
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    public function getTransactionDate()
    {
        return $this->transaction_date;
    }

    // Setter methods
    public function setPaymentId($payment_id)
    {
        $this->payment_id = $payment_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setTotalAmount($total_amount)
    {
        $this->total_amount = $total_amount;
    }

    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
    }

    public function setPaymentStatus($payment_status)
    {
        $this->payment_status = $payment_status;
    }

    public function setTransactionDate($transaction_date)
    {
        $this->transaction_date = $transaction_date;
    }

    // Get a list of pending refund requests
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

    // Update refund details and related tables
    function updateRefund($request_id, $status, $current_date, $user_id)
    {
        // Start a transaction
        $this->conn->begin_transaction();

        try {
            if ($status === 'Rejected') {
                // Delete the refund record if the status is Rejected
                $sqlDeleteRefund = "DELETE FROM refund WHERE refund_id = ?";
                $stmtDeleteRefund = $this->conn->prepare($sqlDeleteRefund);
                $stmtDeleteRefund->bind_param("i", $request_id);

                if (!$stmtDeleteRefund->execute()) {
                    throw new Exception("Failed to delete refund record.");
                }
            } else if ($status === 'Approved') {
                // Update refund status in the refund table
                $sqlRefund = "UPDATE refund 
                              SET refund_status = ?, refund_approve_date = ?, approve_by = ? 
                              WHERE refund_id = ?";
                $stmtRefund = $this->conn->prepare($sqlRefund);
                $stmtRefund->bind_param("ssii", $status, $current_date, $user_id, $request_id);

                if (!$stmtRefund->execute()) {
                    throw new Exception("Failed to update refund table.");
                }

                // Update the product_order table
                $sqlProductOrder = "UPDATE product_order 
                                    SET status = 'Refunded' 
                                    WHERE order_id = (
                                        SELECT order_id 
                                        FROM refund 
                                        WHERE refund_id = ?
                                    ) 
                                    AND product_id = (
                                        SELECT product_id 
                                        FROM refund 
                                        WHERE refund_id = ?
                                    )";
                $stmtProductOrder = $this->conn->prepare($sqlProductOrder);
                $stmtProductOrder->bind_param("ii", $request_id, $request_id);

                if (!$stmtProductOrder->execute()) {
                    throw new Exception("Failed to update product_order table.");
                }

                // Update the shipment table
                $sqlShipment = "UPDATE shipment 
                                SET status = 'Cancelled' 
                                WHERE order_id = (
                                    SELECT order_id 
                                    FROM refund 
                                    WHERE refund_id = ?
                                )";
                $stmtShipment = $this->conn->prepare($sqlShipment);
                $stmtShipment->bind_param("i", $request_id);

                if (!$stmtShipment->execute()) {
                    throw new Exception("Failed to update shipment table.");
                }
            }

            // Commit the transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $this->conn->rollback();
            error_log($e->getMessage());
            return false;
        }
    }

    // Get the refund percentage for a vendor or admin
    function getRefundPercentage($vendor_id)
    {
        $currentYear = date("Y");

        if ($vendor_id == -1) {
            $sqlRefunds = "
                SELECT COUNT(*) AS totalRefunds
                FROM refund
                WHERE YEAR(refund_date) = '$currentYear' AND refund_status= 'Approved'
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
                AND product.vendor_id = '$vendor_id' AND refund_status= 'Approved'
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

    // Get revenue data for analytics
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
            $sql = "SELECT $select, 
                    SUM(IF(r.refund_id IS NULL, po.sub_price, 0)) AS revenue
            FROM orders o
            JOIN product_order po ON o.order_id = po.order_id
            LEFT JOIN refund r ON r.order_id = po.order_id 
                                AND r.product_id = po.product_id 
                                AND r.refund_status = 'Approved'
            WHERE YEAR(order_date) = ?
            GROUP BY $groupBy
            ORDER BY $orderBy";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $currentYear);
        } else {
            // Vendor
            $sql = "SELECT $select, 
                   SUM(IF(r.refund_id IS NULL, po.sub_price, 0)) AS revenue
            FROM orders o
            JOIN product_order po ON o.order_id = po.order_id
            JOIN product p ON po.product_id = p.product_id
            LEFT JOIN refund r ON r.order_id = po.order_id 
                               AND r.product_id = po.product_id 
                               AND r.refund_status = 'Approved'
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

    // Get the top payment methods
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

    // Get order data for analytics
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
            $sql = "
            SELECT duration, COUNT(*) AS total_orders
            FROM (
                SELECT o.order_id, $select
                FROM orders o
                JOIN product_order po ON o.order_id = po.order_id
                LEFT JOIN refund r 
                    ON r.order_id = o.order_id 
                    AND r.product_id = po.product_id 
                    AND r.refund_status = 'Approved'
                WHERE YEAR(o.order_date) = ?
                GROUP BY o.order_id
                HAVING COUNT(DISTINCT po.product_id) > COUNT(DISTINCT r.refund_id)
            ) AS valid_orders
            GROUP BY duration
            ORDER BY duration
        ";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $currentYear);
        } else {
            // Vendor
            $sql = "
            SELECT duration, COUNT(*) AS total_orders
            FROM (
                SELECT o.order_id, $select
                FROM orders o
                JOIN product_order po ON o.order_id = po.order_id
                JOIN product p ON po.product_id = p.product_id
                LEFT JOIN refund r 
                    ON r.order_id = o.order_id 
                    AND r.product_id = po.product_id 
                    AND r.refund_status = 'Approved'
                WHERE YEAR(o.order_date) = ? AND p.vendor_id = ?
                GROUP BY o.order_id
                HAVING COUNT(DISTINCT po.product_id) > COUNT(DISTINCT r.refund_id)
            ) AS valid_orders
            GROUP BY duration
            ORDER BY duration
        ";

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

    // Get payment details by order ID
    function getPaymentDetails($order_id)
    {
        $sql = "SELECT payment_id, payment_method, payment_status 
                FROM payment 
                WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update the payment status
    function updatePaymentStatus($payment_id, $status)
    {
        $sql = "UPDATE payment 
                SET payment_status = ? 
                WHERE payment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $payment_id);
        return $stmt->execute();
    }
}
