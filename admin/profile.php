<?php
session_start();
include 'db/dbcon.php';

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: ../login"); // যদি না থাকে, লগইন পেজে পাঠিয়ে দিবো
    exit();
}

$user = $_SESSION['auth_user']; // লগইন করা ব্যবহারকারীর তথ্য

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

    <div class="container">
        <div class="button">
            <a href="javascript:window.history.back()">Back</a>
        </div>
        
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <div class="profile-info">
            <label for="name">Name:</label>
            <p><?php echo htmlspecialchars($user['name']); ?></p>
        </div>
        
        <div class="profile-info">
            <label for="email">Email:</label>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="profile-info">
            <label for="phone">Phone:</label>
            <p><?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
        
        <div class="profile-info">
            <label for="phone">User Role:</label>
            <p><?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        
    </div>

</body>
</html>
