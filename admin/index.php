<?php
session_start();
include 'db/dbcon.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);


// যদি 'message' সেট না থাকে, তাহলে এটি অ্যারে হিসেবে সেট করবো
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}


if (!isset($_SESSION['auth_user']['id']) && $_SESSION['auth_user']['role'] !== "admin") {
    header("Location: ../login");
    exit();
}
$user_id = $_SESSION['auth_user']['id'];

$query = "
         SELECT creditors.id, users.name AS user_name, creditors.name, creditors.contact, creditors.amount, creditors.passkey 
    FROM creditors 
    JOIN users ON creditors.user_id = users.id 
    ORDER BY creditors.id DESC;
";
$result = mysqli_query($con, $query);

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Success Message
            var successMessage = document.getElementById('success-message');
            successMessage.innerHTML = '✅ Data has been deleted successfully! <span class=\"close-btn\" onclick=\"closeSuccessMessage()\">❌</span>';
            successMessage.style.display = 'block';
            successMessage.style.color = 'red';
        });
    </script>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debt Management</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        /* Common Alert Style */
        .alert {
            width: 40%;
            margin: 10px auto;
            padding: 15px;
            border-radius: 5px;
            position: relative;
            display: none;
            font-size: 16px;
            font-weight: bold;
        }
        .alert .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        /* ✅ Success Alert */
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-success .close-btn { color: #155724; }

        /* ⚠️ Warning Alert */
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .alert-warning .close-btn { color: #856404; }

        /* ❌ Danger Alert */
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-danger .close-btn { color: #721c24; }

    </style>
</head>
<body>
   <?php include 'includes/header.php'; ?> 

<?php
// Check if any message is set
if (isset($_SESSION['message'])) {
    foreach ($_SESSION['message'] as $type => $msg) {
        echo "<div class='alert alert-$type' id='alertMessage'>
                <span>$msg</span>
                <span class='close-btn' onclick='closeAlert()'>❌</span>
              </div>";
    }
    // Message একবার দেখানোর পর মুছে ফেলবো
    unset($_SESSION['message']);
}
?>

<h2>Welcome to Debt Management</h2>



<!-- Dena List -->
<div class="denaList">
    <table>
         <tr>
              <td colspan="6">Debitor and Creditor</td>
        </tr>
        <tr>
            <th>S.N</th>
            <th>User Name</th>
            <th>People Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
        <tbody>
        <?php 
        $sn = 1;
        while ($row = mysqli_fetch_assoc($result)) { 
            $type = ($row['amount'] < 0) ? "Dena" : (($row['amount'] > 0) ? "Pawna" : "Finished");
            $color = ($row['amount'] < 0) ? "red" : (($row['amount'] > 0) ? "green" : "gray");
            ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                <td style="color: <?php echo $color; ?>;">
                    <?php echo htmlspecialchars($row['amount']); ?>
                </td>
                
                <td>
                    <a href="view_details.php?people_id=<?php echo $row['id']; ?>" class="btn btn-info">View Details</a>
                
                    <a onclick="return confirm('are you sure?')" href="delete_people.php?people_id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
    // এলার্ট শো করবো
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.display = 'block';
    });

    // ৩ সেকেন্ড পর এলার্ট হাইড হবে
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 3000);

    // ম্যানুয়ালি এলার্ট ক্লোজ ফাংশন
    function closeAlert() {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }
</script>
</body>
</html>
