<?php

$host =  "localhost";
$dbUser = "amarsite";
$dbPwd = ";7[Vq3B7NeJp4g";
$dbName = "amarsite_debt_management";

$con = mysqli_connect($host,$dbUser,$dbPwd,$dbName);
if(!$con){
die("Connection failed: ". mysqli_connect_error());
}
?>