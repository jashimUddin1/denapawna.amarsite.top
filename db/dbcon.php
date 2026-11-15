<?php

$host =  "localhost";
$dbUser = "root";
$dbPwd = "";
$dbName = "amarsite_debt_management";

$con = mysqli_connect($host,$dbUser,$dbPwd,$dbName);
if(!$con){
die("Connection failed: ". mysqli_connect_error());
}

$con->set_charset('utf8mb4');
?>