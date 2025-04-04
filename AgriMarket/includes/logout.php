<?php
session_start();

$_SESSION = [];

session_destroy();

header("Location: ../Modules/authentication/login.php");
exit();
?>