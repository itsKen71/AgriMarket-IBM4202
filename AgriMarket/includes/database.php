<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agrimarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

function authenticateUser($username_email, $password)
{
    global $conn;

    $password_hashed = hash('sha256', $password);

    $stmt = $conn->prepare("SELECT user_id, role, password FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['password'] === $password_hashed) {
            // Update the last_online field
            $update_stmt = $conn->prepare("UPDATE user SET last_online = NOW() WHERE user_id = ?");
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

function insertUser($first_name, $last_name, $username, $email, $password, $role, $phone_number, $home_address)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO user (first_name, last_name, username, email, password, role, phone_number, home_address, last_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $email, $password, $role, $phone_number, $home_address);

    return $stmt->execute();
}

function getUsernameFromUserID($user_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT username FROM user WHERE user_id = ?");
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
    global $conn;

    $stmt = $conn->prepare("SELECT email FROM user WHERE username = ?");
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
    global $conn;

    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $username);

    return $stmt->execute();
}

function isVendorTierThree($user_id)
{
    global $conn;

    $stmt = $conn->prepare("
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

function getCategories()
{
    global $conn;

    $sql = "SELECT * FROM category ORDER BY category_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function getApprovedProducts($category_id = null, $search_query = '', $filter = '')
{
    global $conn;

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

    $stmt = $conn->prepare($sql);
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

function getVendorDetailsById($conn, $vendor_id)
{
    $query = "SELECT v.store_name, u.first_name, u.last_name, u.email, u.phone_number 
              FROM vendor v 
              JOIN user u ON v.user_id = u.user_id 
              WHERE v.vendor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getVendorProducts($conn, $vendor_id, $search_query = '', $filter = '')
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

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


/*Staff Dashboard*/
function update_Promotion_Discount($discountCode, $promotionTitle, $promotionMessage, $startDate, $endDate, $discountPercentage, $minPurchaseAmount, $isActive, $created_by)
{
    global $conn;

    //Insert into Discount Table
    $sqlDiscount = "INSERT INTO discount (discount_code, discount_percentage,min_amount_purchase) VALUES (?,?,?)";

    $stmtDiscount = $conn->prepare($sqlDiscount);
    $stmtDiscount->bind_param("sdd", $discountCode, $discountPercentage, $minPurchaseAmount);

    if ($stmtDiscount->execute()) {
        //Get Last Row Discount ID
        $discount_id = $stmtDiscount->insert_id;

        //Insert into Promotion table
        $sqlPromotion = "INSERT INTO promotion(discount_id,promotion_title,promotion_message,promotion_start_date, promotion_end_date, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmtPromotion = $conn->prepare($sqlPromotion);
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

function getCustomerEmails() {
    global $conn;

    $sql="SELECT first_name, last_name, email FROM user WHERE role = 'Customer' ";
    $result=mysqli_query($conn,$sql);

    $customers=[];

    if($result){
        while($row = mysqli_fetch_assoc($result)){
            $fullName = $row['first_name'] . ' ' . $row['last_name'];

            $customers[] = [
                'email' => $row['email'],
                'full_name' => $fullName
            ];

        }
    }
    return $customers;
}


function getVendorList()
{
    global $conn;

    $sql = "SELECT v.vendor_id, v.store_name, s.plan_name, v.subscription_end_date, 
                   IFNULL(CONCAT(u.first_name, ' ', u.last_name), '-') AS staff_assistance, 
                   v.assist_by
            FROM vendor v
            LEFT JOIN user u ON u.user_id = v.assist_by
            JOIN subscription s ON v.subscription_id = s.subscription_id";

    $result = $conn->query($sql);

    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function updateVendorAssistance($vendorId, $staffId)
{
    global $conn;

    $sql = "UPDATE vendor SET assist_by = ? WHERE vendor_id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $staffId, $vendorId);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function getVendorAssisstanceList($user_id)
{
    global $conn;

    $sql = "SELECT v.store_name, r.request_description, r.request_type, r.request_date, r.request_id
            FROM request r 
            JOIN vendor v ON r.vendor_id = v.vendor_id
            WHERE v.assist_by = ? AND r.is_completed = FALSE
            ORDER BY r.request_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


function getRefundList()
{
    global $conn;

    $sql = "SELECT  r.refund_id,r.order_id, p.product_name, r.refund_amount, r.refund_date ,r.reason
            FROM product p
            JOIN refund r ON p.product_id = r.product_id
            JOIN user u ON r.user_id = u.user_id
            WHERE r.refund_status='Pending'
            ORDER BY r.refund_date ASC";

    $result = $conn->query($sql);

    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function updateRefund($request_id,$status,$current_date,$user_id){
    global $conn;

    //Update refund status
    $sql = "UPDATE refund SET refund_status=?, refund_approve_date=?, approve_by=? WHERE refund_id= ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssii", $status, $current_date, $user_id, $request_id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

}

function updateAssisstanceRequestStatus($request_id, $status)
{
    global $conn;

    $sql = "UPDATE request SET is_completed=? WHERE request_id= ?";
    $stmt = $conn->prepare($sql);

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
    global $conn;

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

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function getAdminList()
{

    global $conn;

    $sql = "SELECT CONCAT(first_name , ' ' , last_name) AS Name, phone_number, home_address, last_online FROM user 
          WHERE role='Admin'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function getPendingRequestList()
{
    global $conn;

    $sql = "SELECT v.store_name, c.category_name,p.product_name, p.description, p.stock_quantity,p.weight, p.unit_price, p.product_id
          FROM vendor v JOIN product p
          ON v.vendor_id = p.vendor_id
          JOIN category c 
          ON p.category_id=c.category_id
          WHERE p.product_status='Pending'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function updatePendingRequestStatus($product_id, $status)
{
    global $conn;

    $sql = "UPDATE product SET product_status=? WHERE product_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $product_id);

    return $stmt->execute();
}

function getActiveUser($conn)
{
    $oneMonthAgo = date("Y-m-d H:i:s", strtotime("-1 month"));

    // Get Active Customers
    $sqlCustomers = "SELECT COUNT(*) AS totalCustomers FROM user WHERE role='Customer' AND last_online >= '$oneMonthAgo'";

    // Get Active Vendors
    $sqlVendors = "SELECT COUNT(*) AS totalVendors FROM user WHERE role='Vendor' AND last_online >= '$oneMonthAgo'";

    $resultCustomers = mysqli_query($conn, $sqlCustomers);
    $resultVendors = mysqli_query($conn, $sqlVendors);

    return [
        "activeCustomers" => mysqli_fetch_assoc($resultCustomers)['totalCustomers'],
        "activeVendors" => mysqli_fetch_assoc($resultVendors)['totalVendors']
    ];
}

function getRefundPercentage($conn, $user_id)
{
    $currentYear = date("Y");

    //Check whether is admin / vendor
    $vendorView = ($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

    $sqlRefunds = "SELECT COUNT(*) AS totalRefunds 
                    FROM refund 
                    JOIN orders ON refund.order_id =orders.order_id
                    WHERE YEAR(refund_date) = '$currentYear' $vendorView";

    $sqlOrders = "SELECT COUNT(*) AS totalOrders FROM orders WHERE YEAR(order_date) = '$currentYear' $vendorView";

    $resultRefunds = mysqli_query($conn, $sqlRefunds);
    $resultOrders = mysqli_query($conn, $sqlOrders);

    $totalRefunds = mysqli_fetch_assoc($resultRefunds)['totalRefunds'];
    $totalOrders = mysqli_fetch_assoc($resultOrders)['totalOrders'];

    $refundPercentage = ($totalOrders > 0) ? ($totalRefunds / $totalOrders) * 100 : 0;

    return ["totalRefundPercentage" => round($refundPercentage, 2)];
}

function getRevenue($conn, $user_id)
{
    $currentYear = date("Y");

    //Check whether is admin / vendor
    $vendorView = ($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

    $sql = "SELECT MONTH(order_date) AS month, SUM(price) AS revenue 
            FROM orders 
            WHERE YEAR(order_date) = '$currentYear' $vendorView 
            GROUP BY MONTH(order_date)";

    $result = mysqli_query($conn, $sql);
    $months = [
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

    $data = [];

    foreach ($months as $num => $name) {
        $data[$num] = ["month" => $name, "revenue" => 0];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $monthName = $months[$row['month']];
        $data[$row['month']] = ["month" => $monthName, "revenue" => $row['revenue']];
    }

    return array_values($data);
}

function getOrders($conn, $user_id)
{
    $currentYear = date("Y");

    //Check whether is admin / vendor
    $vendorView = ($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

    $months = [
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

    $sql = "SELECT MONTH(order_date) AS month, COUNT(order_id) AS totalOrder 
            FROM orders 
            WHERE YEAR(order_date) = '$currentYear' $vendorView
            GROUP BY MONTH(order_date)";

    $result = mysqli_query($conn, $sql);
    $data = [];

    foreach ($months as $num => $name) {
        $data[$num] = ["month" => $name, "total_orders" => 0];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $monthName = $months[$row['month']];
        $data[$row['month']] = ["month" => $monthName, "total_orders" => $row['totalOrder']];
    }

    return array_values($data);
}

function getSubscription($conn)
{
    $sql = "SELECT s.plan_name, COUNT(v.vendor_id) AS totalUsers 
            FROM vendor v JOIN subscription s 
            ON v.subscription_id = s.subscription_id
            GROUP BY s.plan_name
            ORDER BY totalUsers DESC";

    $result = mysqli_query($conn, $sql);
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

function topPaymentmethod($conn){
    $sql = "SELECT payment_method, COUNT(*) AS frequency
            FROM payment 
            GROUP BY payment_method 
            ORDER BY frequency DESC";

    $result = mysqli_query($conn, $sql);
    $rows = [];
    $totalFrequency = 0;

    // Fetch all data once
    while($row = mysqli_fetch_assoc($result)){
        $rows[] = $row;
        $totalFrequency += $row['frequency'];
    }

    // Build pie chart data
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

function getTopVendor($conn){
    $sql = "SELECT v.store_name AS label, SUM(po.quantity) AS quantity
    FROM product_order po
    JOIN product p ON po.product_id = p.product_id
    JOIN vendor v ON p.vendor_id = v.vendor_id
    GROUP BY v.vendor_id
    ORDER BY quantity DESC
    LIMIT 5";

    $result = mysqli_query($conn, $sql);
    $data = []; 

    while($row = mysqli_fetch_assoc($result)){
        $data[] = [ 
            "label" => $row['label'],
            "y" => (int) $row['quantity']
        ];
    }
    return $data;
}


function getProductsByStatus($conn, $vendor_id, $status)
{
    $sql = "
        SELECT p.product_id, p.product_name, p.description, p.stock_quantity, p.weight, p.unit_price, p.product_status, p.product_image,
        p.product_status,
        c.category_name
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        WHERE p.vendor_id = ? AND p.product_status = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $vendor_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}

function getVendorDetails($user_id, $conn)
{
    $query = "
        SELECT v.vendor_id, v.store_name, v.subscription_id, v.subscription_start_date, v.subscription_end_date, v.staff_assisstance_id,
               u.user_id, u.email, u.phone_number, 
               s.plan_name, s.has_staff_support, s.upload_limit, s.has_low_stock_alert
        FROM vendor v
        JOIN user u ON v.user_id = u.user_id
        LEFT JOIN subscription s ON v.subscription_id = s.subscription_id
        WHERE v.user_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getLowStockProducts($vendor_id, $conn, $threshold = 10)
{
    $query = "
        SELECT product_id, product_name, stock_quantity 
        FROM product 
        WHERE vendor_id = ? AND stock_quantity < ? AND product_status = 'Approved'
        ORDER BY stock_quantity ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $vendor_id, $threshold);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPendingProductCount($vendor_id, $conn)
{ // Used to determine whether it exceed upload_limit
    $query = "SELECT COUNT(*) AS pending_count FROM product WHERE vendor_id = ? AND product_status = 'Pending'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['pending_count'] ?? 0; // Return 0 if no result
}

function insertRequest($conn, $vendor_id, $request_type, $request_description)
{
    $query = "INSERT INTO request (vendor_id, request_type, request_description, request_date) 
              VALUES (?, ?, ?, NOW())";

    if ($stmt = $conn->prepare($query)) {
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

function updateVendorProfile($conn, $store_name, $vendor_id)
{
    $query = "UPDATE vendor SET store_name = ? WHERE vendor_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("si", $store_name, $vendor_id);
        return $stmt->execute(); // Return true if success, false if failure
    }
    return false;
}

function updateUserDetails($conn, $email, $phone_number, $user_id)
{
    $query = "UPDATE user SET email = ?, phone_number = ? WHERE user_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssi", $email, $phone_number, $user_id);
        return $stmt->execute(); // Return true if success, false if failure
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

function updateProduct($conn, $product_id, $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status)
{
    $query = "UPDATE product 
              SET category_id = ?, product_image = ?, description = ?, stock_quantity = ?, weight = ?, unit_price = ? , product_status = ? 
              WHERE product_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("issiddsi", $category_id, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status, $product_id);
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

function insertProduct($conn, $vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status)
{
    $query = "INSERT INTO product (vendor_id, category_id, product_name, product_image, description, stock_quantity, weight, unit_price, product_status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iisssidds", $vendor_id, $category_id, $product_name, $image_path, $description, $stock_quantity, $weight, $unit_price, $product_status);
        return $stmt->execute(); // Return true if successful, false if failed
    }
    return false;
}

function checkIfVendor($conn, $user_id)
{ // Check if user is already a vendor
    $query = "SELECT vendor_id FROM vendor WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function upgradeToVendor($conn, $user_id, $plan_id, $end_date)
{ // Upgrade user to vendor and insert into vendor table
    // Upgrade user role
    $update_user = "UPDATE user SET role = 'Vendor' WHERE user_id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute())
        return false;

    // Insert into vendor table
    $insert_vendor = "INSERT INTO vendor (user_id, subscription_id, store_name, subscription_start_date, subscription_end_date)
                      VALUES (?, ?, 'New Store', CURDATE(), ?)";
    $stmt = $conn->prepare($insert_vendor);
    $stmt->bind_param("iis", $user_id, $plan_id, $end_date);
    return $stmt->execute();
}

function updateVendorSubscription($conn, $user_id, $plan_id, $end_date)
{ // Update vendor's subscription
    $update_vendor = "UPDATE vendor SET subscription_id = ?, subscription_start_date = CURDATE(), subscription_end_date = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_vendor);
    $stmt->bind_param("isi", $plan_id, $end_date, $user_id);
    return $stmt->execute();
}

function getPlanName($conn, $plan_id)
{ // Get subscription plan name
    $query = "SELECT plan_name FROM subscription WHERE subscription_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();
    return $plan ? $plan['plan_name'] : null;
}

function getOrderHistoryByUser($userId, $conn)
{
    $sql = "
        SELECT o.order_id, o.order_date, o.price AS total_order_price,
            poh.product_id, poh.quantity, poh.sub_price,
            p.product_name, p.unit_price, p.product_image, p.stock_quantity,
            coh.status AS order_status
        FROM orders o
        INNER JOIN product_order poh ON o.order_id = poh.order_id
        INNER JOIN product p ON poh.product_id = p.product_id
        INNER JOIN customer_order_history coh ON o.order_id = coh.order_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC, o.order_id DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'order_date' => $row['order_date'],
                'total_order_price' => $row['total_order_price'],
                'order_status' => $row['order_status'],
                'products' => []
            ];
        }
        $orders[$orderId]['products'][] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'product_image' => $row['product_image'],
            'unit_price' => $row['unit_price'],
            'quantity' => $row['quantity'],
            'sub_price' => $row['sub_price'],
            'stock_quantity' => $row['stock_quantity']
        ];
    }
    return $orders;
}
