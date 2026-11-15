<?php // reset_password_code.php 
session_start();
require '../db/dbcon.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if (isset($_POST['reset_password_btn'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // ইউজারের তথ্য চেক করা
    $check_email_query = "SELECT reset_token FROM `users` WHERE email='$email' LIMIT 1";
    $check_email_query_run = mysqli_query($con, $check_email_query);
    
    if (mysqli_num_rows($check_email_query_run) > 0) {
        $user_data = mysqli_fetch_assoc($check_email_query_run);
        
        // যদি আগের টোকেন থাকে, তাহলে নতুন টোকেন না পাঠিয়ে মেসেজ দেখাবে
        if (!empty($user_data['reset_token'])) {
            $_SESSION['status'] = "Password reset token already sent to your email. Please wait 5 minutes or contact Developer Jasim.";
            header("Location: forgot_password.php");
            exit(0);
        }

        // নতুন রিসেট টোকেন তৈরি করা
        $token = md5(rand());
        $update_token_query = "UPDATE `users` SET reset_token='$token' WHERE email='$email'";
        
        if (mysqli_query($con, $update_token_query)) {
            // ইমেইল পাঠানোর সেটআপ
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'denapawna.amarsite.top';
            $mail->SMTPAuth = true;
            $mail->Username = 'noreply@denapawna.amarsite.top';
            $mail->Password = 'TFdU9e;Ap*66'; 
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('noreply@denapawna.amarsite.top', 'Developer Jasim');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Reset Password";
            $mail->Body = "
                <h4>Reset your password</h4>
                <p>Click the link below to reset your password:</p>
                <a href='https://denapawna.amarsite.top/login/reset_password.php?token=$token'>Reset Password</a>
                <p>Thanks in Advance. Enjoy 🙂😊</p>
            ";

            if ($mail->send()) {
                $_SESSION['status'] = "Password reset link sent! Check your email.";
                header("Location: forgot_password.php");
                exit(0);
            } else {
                $_SESSION['status'] = "Failed to send email.";
                header("Location: forgot_password.php");
                exit(0);
            }
        }
    } else {
        $_SESSION['status'] = "No account found with this email.";
        header("Location: forgot_password.php");
        exit(0);
    }
}
?>