<!-- register.php -->
<?php
session_start();
if (isset($_SESSION['authenticated'])) {
    header("location: ../index.php");
}
$page_title = "Registration Page";

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
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Registration Form</h5>
                        <button onclick="window.history.back();" class="btn btn-secondary">Back</button>
                    </div>
                    <div class="card-body">
                        <form action="register_code.php" method="post">
                            <div class="form-group">
                                <label for="">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="">Phone Number</label>
                                <input type="number" name="phone" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
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
                            <div class="form-group">
                                <button type="submit" name="register_btn" class="btn btn-primary">Register Now</button>
                            </div>
                        </form>
                        <hr>
                        <h5 class="mx-auto">
                            Already have an account? Click <a href="index.php">Here</a> to Login
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