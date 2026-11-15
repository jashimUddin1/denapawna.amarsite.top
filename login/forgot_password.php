<?php 
session_start();
$page_title = "Forgot Password";
require '../db/dbcon.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php 
                    if(isset($_SESSION['status'])) {
                        echo "<div class='alert alert-success'>".$_SESSION['status']."</div>";
                        unset($_SESSION['status']);
                    }
                ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between ">
                        <h4>Reset Password</h4>
                        <button onclick="window.history.back();" class="btn btn-secondary">Back</button>
                    </div>
                    <div class="card-body">
                        <form action="reset_password_code.php" method="POST">
                            <div class="form-group mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="reset_password_btn" class="btn btn-primary">Send Reset Link</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
