<!-- index.php -->
<?php
session_start();
if (isset($_SESSION['authenticated'])) {
    header("location: ../index.php");
}

$page_title = "Login Page";
include('includes/header.php');
?>

<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <?php
                if (isset($_SESSION['status'])) {
                ?>
                    <div class="alert alert-success">
                        <h5><?= $_SESSION['status'] ?></h5>
                    </div>
                <?php
                    unset($_SESSION['status']);
                }
                ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Login form</h5>
                        <button onclick="window.history.back();" class="btn btn-secondary">Back</button>
                    </div>
                    <div class="card-body">
                        <form action="logincode.php" method="POST">
                            <div class="form-group mb-3">
                                <label for="">Email Address</label>
                                <input type="text" name="email" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password" class="form-control" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        Show
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="login_now_btn" class="btn btn-primary">Login Now</button>
                                <a href="forgot_password.php" class="btn btn-primary">Forgot Password?</a>
                            </div>
                    </div>
                    </form>
                    <hr>

                    <!-- <h5 class="mx-auto">
                            Did not receive your verification Email
                            <a href="resend-email.php">Resend</a>
                        </h5> -->
                    <h5 class="mx-auto">
                        Did not account? please Register to Click
                        <a href="register.php">Here</a>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        // Password field type toggle (password <-> text)
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Button text toggle (Show <-> Hide)
        togglePassword.textContent = type === 'password' ? 'Show' : 'Hide';
    });
</script>


<?php include('includes/footer.php'); ?>