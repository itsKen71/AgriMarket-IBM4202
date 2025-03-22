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

?>