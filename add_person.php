<?php
session_start();
include 'db/dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['auth_user']['id'])) {
        header("Location: login");
        exit();
    }

    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $user_id = $_SESSION['auth_user']['id'];

    $passkey = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@£$%^&*()_+"), 0, 8);

    $stmt = $con->prepare("INSERT INTO creditors (user_id, name, contact, amount, passkey) VALUES (?, ?, ?, 0, ?)");
    $stmt->bind_param("isss", $user_id, $name, $contact, $passkey);
    $stmt->execute();

    echo "success"; 
}
?>