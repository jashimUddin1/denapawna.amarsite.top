<?php

$host =  "localhost";
$dbUser = "amarsite";
$dbPwd = "t(=C!u%CcK{6L%T(qw";
$dbName = "amarsite_debt_management";

$con = mysqli_connect($host,$dbUser,$dbPwd,$dbName);
if(!$con){
die("Connection failed: ". mysqli_connect_error());
}
?>