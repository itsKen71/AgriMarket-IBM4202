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

function getVendorList(){
    global $conn;

    $sql="SELECT v.store_name, s.plan_name, v.subscription_end_date, 
          IFNULL(CONCAT(u.first_name, ' ', u.last_name), '-') AS staff_assistance
          FROM vendor v
          LEFT JOIN user u ON u.user_id = v.staff_assisstance_id
          JOIN subscription s ON v.subscription_id = s.subscription_id";

    $result= $conn->query($sql);

    if($result->num_rows>0){
        return $result->fetch_all(MYSQLI_ASSOC);
    }else{
        return [];
    }
}

function getStaffList(){
        global $conn;

        $sql="SELECT CONCAT(first_name, ' ' ,last_name) AS Name, last_online
              FROM user
              WHERE role='Staff'
              ORDER BY last_online DESC";

        $result=$conn ->query($sql);

        if($result->num_rows>0){
            return $result->fetch_all(MYSQLI_ASSOC);
        }else{
            return [];
        }
}

function getPendingRequestList(){
    global $conn;

    $sql="SELECT v.store_name, c.category_name,p.product_name, p.description, p.stock_quantity,p.weight, p.unit_price, p.product_id
          FROM vendor v JOIN product p
          ON v.vendor_id = p.vendor_id
          JOIN category c 
          ON p.category_id=c.category_id
          WHERE p.product_status='Pending'";

          $result=$conn ->query($sql);

          if($result ->num_rows>0){
            return $result-> fetch_all(MYSQLI_ASSOC);
          }else{
            return [];
          }
}

function updatePendingRequestStatus($product_id,$status){
        global $conn;

        $sql="UPDATE product SET product_status=? WHERE product_id=?";

        $stmt=$conn->prepare($sql);
        $stmt->bind_param("si",$status,$product_id);

        return $stmt->execute();
}

function getVendorAssisstanceList($user_id){
    global $conn;

    $sql="SELECT v.store_name,r.request_description, r.request_type,r.request_date, r.request_id
          FROM request r JOIN vendor v
          ON r.vendor_id = v.vendor_id
          WHERE v.staff_assisstance_id=? AND r.is_completed=FALSE";

    $stmt =$conn->prepare($sql);
    $stmt ->bind_param("i",$user_id);
    $stmt ->execute();

    $result= $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateAssisstanceRequestStatus($request_id,$status){
    global $conn;

    $sql="UPDATE request SET is_completed=? WHERE request_id= ?";
    $stmt= $conn->prepare($sql);

    //Convert Boolean to Integer 
    $status =(int)$status;

    $stmt->bind_param("ii",$status,$request_id);

    if($stmt->execute()){
        return true;
    }else {
        return false;
    }
}

?>