<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>

<header class="navbar">
    <div class="logo">
        <a href="index.php">Debit Credit Management</a>
    </div>
    <nav>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <!-- <li><a href="#" id="addNewBtn">Add New</a></li> -->
             <?php if($_SESSION['auth_user']['role'] !== "admin") {?>
                <li><a href="help.php">Help</a></li>
            <?php } ?>
            
            <?php if(isset($_SESSION['auth_user'])): 
                $user_name = explode(' ', $_SESSION['auth_user']['name'])[0]; // প্রথম শব্দটি নিতে
            ?>
                <li class="user-name">
                    <a href="user"><i class="fa-solid fa-user"></i> 
                        <span class="desktop-name"><?php echo htmlspecialchars($_SESSION['auth_user']['name']); ?></span>
                        <span class="mobile-name"><?php echo htmlspecialchars($user_name); ?></span>
                    </a>
                </li>
                
            <?php else: ?>
                <li><a href="login">Login/Register</a></li>
            <?php endif; ?>
            
            <?php if($_SESSION['auth_user']['role'] === "admin") {?>
                <li><a href="admin">Admin</a></li>
            <?php } ?>
        </ul>
        <div class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </div>
    </nav>
</header>

<script>
    const menuToggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    menuToggle.addEventListener("click", () => {
        navLinks.classList.toggle("active");
    });
</script>

</body>
</html>