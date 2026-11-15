<?php
session_start();
require '../db/dbcon.php';

if (isset($_POST['update_password_btn'])) {
    $token = mysqli_real_escape_string($con, $_POST['token']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_query = "UPDATE `users` SET password='$hashed_password', reset_token='' WHERE reset_token='$token'";
        if (mysqli_query($con, $update_password_query)) {
            $_SESSION['status'] = "Password updated successfully! Please login.";
            header("Location: index.php");
        } else {
            $_SESSION['status'] = "Failed to update password.";
            header("Location: reset_password.php?token=$token");
        }
    } else {
        $_SESSION['status'] = "Passwords do not match.";
        header("Location: reset_password.php?token=$token");
    }
}
?>
