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

    $sql="SELECT v.store_name, s.plan_name,v.subscription_end_date,v.staff_assisstance
           FROM vendor v JOIN subscription s
           ON v.subscription_id=s.subscription_id";

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


?>