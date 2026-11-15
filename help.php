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
    <style type="text/css">
        body{
        margin:0;
        padding:0;
        }
        .helpContent{
        
        }
        .helpContent h4{
            font-size:25px;
            text-align:center;
        }
        .helpContent p{
            width:90%;
            margin:auto;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">
        <a href="index.php">Debt Management</a>
    </div>
    <nav>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="#" id="addNewBtn">Add New</a></li>
            <li><a href="help.php">Help</a></li>
            <?php if(isset($_SESSION['auth_user'])): 
                $user_name = explode(' ', $_SESSION['auth_user']['name'])[0]; // প্রথম শব্দটি নিতে
            ?>
                <li class="user-name">
                    <a href="users.php"><i class="fa-solid fa-user"></i> 
                        <span class="desktop-name"><?php echo htmlspecialchars($_SESSION['auth_user']['name']); ?></span>
                        <span class="mobile-name"><?php echo htmlspecialchars($user_name); ?></span>
                    </a>
                </li>
                <li><a class="logout-btn" href="login/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login">Login/Register</a></li>
            <?php endif; ?>
        </ul>
        <div class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </div>
    </nav>
</header>
<section>
    <div class="helpContent">
        <h4>কিভাবে কাজ করে</h4>
        <p>প্রথমত অ্যাকাউন্ট তৈরি করে ভেরিফাই করে নিতে হবে ভেরিফাই হয়ে গেলে লগইন করতে হবে 
        </p>
<br>
        <p>
            লেনদেন তৈরি করার জন্য এড নিউ বাটনে ক্লিক করতে হবে সেখান থেকে লেনদেন কারীর নাম দিয়ে দেনাদার বা পাওনাদার তৈরি করে নিতে হবে
        </p>
        <br>
      <p>  
            দেনাদার বা পাওনাদার তৈরি হয়ে গেলে 
           Finished List এ চলে যাবে সেখান থেকে  view details বাটনে ক্লিক করে লেনদেন তৈরি করা , এডিট করা ও ডিলিট করা যাবে 
       </p>
    </div>
</section>
<script>
    const menuToggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    menuToggle.addEventListener("click", () => {
        navLinks.classList.toggle("active");
    });
</script>

</body>
</html>
