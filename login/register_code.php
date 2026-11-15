<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db/dbcon.php';
require 'vendor/autoload.php'; // PHPMailer লোড করা

use PHPMailer\PHPMailer\PHPMailer;

if (isset($_POST['register_btn'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    if (strlen($phone) < 11) {
        $_SESSION['status'] = "phone number must be 11 digit";
        header("Location: register.php");
        exit(0);
    }
    if (strlen($password) < 6) {
        $_SESSION['status'] = "Password must be at least 6 characters.";
        header("Location: register.php");
        exit(0);
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // পাসওয়ার্ড হ্যাশ করা
    $verify_token = md5(rand());

    // ইমেইল আগে থেকেই আছে কিনা চেক করা
    $check_email_query = "SELECT email FROM `users` WHERE email='$email' LIMIT 1";
    $check_email_query_run = mysqli_query($con, $check_email_query);

    if (mysqli_num_rows($check_email_query_run) > 0) {
        $_SESSION['status'] = "Email Id already exists.";
        header("Location: register.php");
        exit(0);
    } else {
        $query = "INSERT INTO `users`(`name`, `phone`, `email`, `password`, `verify_token`, `role`) VALUES ('$name', '$phone', '$email', '$hashed_password', '$verify_token', 'user')";
        $query_run = mysqli_query($con, $query);

        if ($query_run) {
            // ইমেইল ভেরিফিকেশন পাঠানোর ফাংশন
            if (sendVerificationEmail($email, $verify_token)) {
                $_SESSION['status'] = "Registration successful! Please check your email to verify your account.";
                header("Location: index.php");
                exit(0);
            } else {
                $_SESSION['status'] = "Failed to send verification email.";
                header("Location: register.php");
                exit(0);
            }
        } else {
            $_SESSION['status'] = "Registration failed.";
            header("Location: register.php");
            exit(0);
        }
    }
}

function sendVerificationEmail($email, $verify_token) {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'denapawna.amarsite.top'; // Gmail SMTP হোস্ট
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@denapawna.amarsite.top';
    $mail->Password = 'TFdU9e;Ap*66';
    $mail->SMTPSecure = 'ssl'; // নিরাপত্তার জন্য TLS ব্যবহার
    $mail->Port = 465; // TLS পোর্ট

    $mail->setFrom('noreply@denapawna.amarsite.top', 'Developer Jasim');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Verify your email";
    $mail->Body = "
        <h2>Welcome to our website!</h2>
        <p>Click the link below to verify your email address:</p>
        <a href='https://denapawna.amarsite.top/login/verify.php?token=$verify_token'>Verify Email</a>

        <p> Thanks in Advance. Enjoy 🙂😊</p>
    ";


    return $mail->send();
}

?>