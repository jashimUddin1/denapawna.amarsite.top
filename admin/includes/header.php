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
    <style>
        .mobile-name{
            display:none;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">
        <a href="#"><i class="fa-solid fa-user"></i> 
                        <span class="desktop-name"><?php echo htmlspecialchars($_SESSION['auth_user']['name']); ?></span>
                        <span class="mobile-name"><?php echo htmlspecialchars($_SESSION['auth_user']['name']); ?></span>
                    </a>
    </div>
    <nav>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="index.php">People</a></li>
            <li>
                <a class="logout-btn" href="login/logout.php">Logout</a>
            </li>
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
