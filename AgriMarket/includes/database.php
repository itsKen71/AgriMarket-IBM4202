<?php
$servername = "localhost";
$username = "root";
$password = "-";
$dbname = "AgriMarket";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

function generateUserID()
{
    global $conn;

    $searchUserIDDQL = "SELECT userID FROM user ORDER BY userID DESC LIMIT 1";
    $result = $conn->query($searchUserIDDQL);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastUserID = $row['userID'];

        $numericPart = (int) substr($lastUserID, 1);
        $nextNumericPart = $numericPart + 1;

        $nextUserID = 'U' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
    } else {
        $nextUserID = 'U001';
    }

    return $nextUserID;
}


?>