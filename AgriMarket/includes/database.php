<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agrimarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

function getUserRole($user_id)
{
    global $conn;

    $getUserRoleSQL = "SELECT role FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($getUserRoleSQL);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    return $row['role'];
}

function insertUser($first_name, $last_name, $email, $password, $role, $phone_number, $home_address)
{
    global $conn;

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (first_name, last_name, email, password, role, phone_number, home_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $password_hash, $role, $phone_number, $home_address);

    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        return false;
    }
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

function getApprovedProducts($category_id = null)
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

    $sql .= " ORDER BY RAND()";

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

function getVendorList()
{
    global $conn;

    $sql = "SELECT v.vendor_id, v.store_name, s.plan_name, v.subscription_end_date, 
            IFNULL(CONCAT(u.first_name, ' ', u.last_name), '-') AS staff_assistance, v.staff_assisstance_id
            FROM vendor v
            LEFT JOIN user u ON u.user_id = v.staff_assisstance_id
            JOIN subscription s ON v.subscription_id = s.subscription_id";

    $result = $conn->query($sql);

    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


function updateVendorAssistance($vendor_id, $staff_id)
{
    global $conn;

    $sql = "UPDATE vendor SET staff_assisstance_id = ? WHERE vendor_id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $staff_id, $vendor_id);
    return $stmt->execute();
}

function getVendorAssisstanceList($user_id)
{
    global $conn;

    $sql = "SELECT v.store_name,r.request_description, r.request_type,r.request_date, r.request_id
          FROM request r JOIN vendor v
          ON r.vendor_id = v.vendor_id
          WHERE v.staff_assisstance_id=? AND r.is_completed=FALSE
          ORDER BY r.request_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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
            COALESCE((SUM(CASE WHEN r.is_completed = 1 THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(r.request_id), 0)), 0) AS progress_percentage
            FROM user u
            LEFT JOIN vendor v ON u.user_id = v.staff_assisstance_id  
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

function getRefundPercentage($conn,$user_id)
{
    $currentYear = date("Y");

    //Check whether is admin / vendor
    $vendorView=($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

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

function getRevenue($conn,$user_id)
{
    $currentYear = date("Y");

    //Check whether is admin / vendor
    $vendorView=($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

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
    $vendorView=($user_id == -1) ? "" : "AND orders.user_id ='$user_id'";

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

function getTopFiveProduct($conn, $user_id)
{
    //Check whether is admin / vendor
    $vendorView=($user_id == -1) ? "" : "WHERE p.vendor_id ='$user_id'";

    $sql = "SELECT p.product_name, SUM(p.sold_quantity) AS totalSold 
            FROM product p 
            $vendorView
            GROUP BY p.product_name
            ORDER BY totalSold DESC
            LIMIT 5";

    $result = mysqli_query($conn, $sql);
    $totalSold = 0;
    $data = [];

    $rows =[];

    //Calculate total sold quantity
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[]=$row;
        $totalSold += $row['totalSold'];
    }

    //Ensure chart will not be showed if less than 5 top product sold
    if(count($rows)<5){
        return[];
    }

    mysqli_data_seek($result, 0);


    while ($row = mysqli_fetch_assoc($result)) {
        $percentage = ($totalSold > 0) ? ($row['totalSold'] / $totalSold) * 100 : 0;
        $data[] = ["label" => $row['product_name'], "y" => round($percentage, 2)];
    }
    return $data;
}


function getProductsByStatus($conn, $vendor_id, $status) 
{
    $sql = "
        SELECT p.product_id, p.product_name, p.description, p.stock_quantity, p.weight, p.unit_price, p.product_status, p.product_image,
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

function getCategory($conn)
{
    $query = "SELECT category_id, category_name FROM category";
    return $conn->query($query);
}

function getVendorDetails($vendor_id, $conn) 
{
    $query = "
        SELECT v.vendor_id, v.store_name, v.subscription_id, v.subscription_start_date, v.subscription_end_date, 
               u.user_id, u.email, u.phone_number, 
               s.plan_name, s.has_staff_support
        FROM vendor v
        JOIN user u ON v.user_id = u.user_id
        LEFT JOIN subscription s ON v.subscription_id = s.subscription_id
        WHERE v.vendor_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc(); 
}
?>