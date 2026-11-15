<?php
session_start();
include 'db/dbcon.php';

if (!isset($_SESSION['auth_user']['id'])) {
    header("Location: ../login");
    exit();
}
$user_id = $_SESSION['auth_user']['id'];

// পাসকি আপডেট করা (রিসাবমিশন এড়ানোর জন্য)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_passkey']) && isset($_POST['people_id'])) {
    $new_passkey = $_POST['new_passkey'];
    $people_id = $_POST['people_id'];

    // ডাটাবেজে পাসকি আপডেট করা
    $update_passkey = $con->prepare("UPDATE creditors SET passkey = ? WHERE id = ? AND user_id = ?");
    $update_passkey->bind_param("sii", $new_passkey, $people_id, $user_id);
    $update_passkey->execute();

    // রিসাবমিশন সমস্যা সমাধান (পেজ রিলোড করলে পুনরায় সাবমিট হবে না)
    header("Location: index.php?passkey_updated=1&success=1");
    exit();
}

// Dena & Pawna এর মোট হিসাব
$total_dena_query = $con->query("SELECT SUM(amount) AS total_dena FROM creditors WHERE user_id = $user_id AND amount < 0");
$total_pawna_query = $con->query("SELECT SUM(amount) AS total_pawna FROM creditors WHERE user_id = $user_id AND amount > 0");

$total_dena = $total_dena_query->fetch_assoc()['total_dena'] ?? 0;
$total_pawna = $total_pawna_query->fetch_assoc()['total_pawna'] ?? 0;

// ডাইনামিক ব্যালেন্স নির্ণয়
$total_balance = $total_pawna + $total_dena;

// সমস্ত creditors লোড করা
$creditors = $con->query("
    SELECT * FROM creditors 
    WHERE user_id = $user_id 
    ORDER BY 
        CASE 
            WHEN amount = 0 THEN 1  
            ELSE 0  
        END,
        amount ASC
");

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debt Management</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
   <?php include 'includes/header.php'; ?> 

<!-- Success Message -->
<div id="success-message" class="success-message">
    ✅ Passkey updated successfully!
    <span class="close-btn" onclick="closeSuccessMessage()">❌</span>
</div>

<?php
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


<h2>Welcome to Debt Management</h2>

<!-- Change Passkey Popup -->
<div id="passkey-popup" class="popup" style="display: none;">
    <div class="popup-content">
        <h3>Change Passkey</h3>
        <form method="POST">
            <input type="hidden" name="people_id" id="people_id">
            <label>New Passkey:</label>
            <input type="text" name="new_passkey" id="new_passkey" required>
            <button type="button" onclick="generatePasskey()">Generate</button>
            <button type="submit">Update</button>
            <button type="button" onclick="closePasskeyPopup()">Cancel</button>
        </form>
    </div>
</div>

<!-- Dena List -->
<div class="denaList">
    <table>
         <tr>
              <td colspan="6">Debitor and Creditor</td>
        </tr>
        <tr>
            <th>S.N</th>
            <th>Name</th>
            <th>Relation</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
        <tbody>
        <?php 
        $sn = 1;
        while ($row = $creditors->fetch_assoc()) { 
            $type = ($row['amount'] < 0) ? "Dena" : (($row['amount'] > 0) ? "Pawna" : "Finished");
            $color = ($row['amount'] < 0) ? "red" : (($row['amount'] > 0) ? "green" : "gray");
            ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['contact']; ?></td>
                <td style="color: <?php echo $color; ?>;"><?php echo $type; ?></td>
                <td style="color: <?php echo $color; ?>;"><?php echo number_format($row['amount']); ?></td>
                <td>
                    <button class="btn change-passkey-btn btn-info" onclick="openPasskeyPopup(<?php echo $row['id']; ?>)">Change Passkey</button>
                    <a onclick="return confirm('are you sure?')" href="delete_people.php?people_id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- JavaScript -->
<script>

// Passkey Popup
function openPasskeyPopup(peopleId) {
    document.getElementById('people_id').value = peopleId;
    document.getElementById('passkey-popup').style.display = 'block';
}
function closePasskeyPopup() {
    document.getElementById('passkey-popup').style.display = 'none';
}
function generatePasskey() {
    let randomPasskey = Math.random().toString(36).substring(2, 10);
    document.getElementById('new_passkey').value = randomPasskey;
}

// Success Message Hide
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        document.getElementById('success-message').style.display = 'block';

        setTimeout(() => {
            document.getElementById('success-message').style.display = 'none';
            history.replaceState(null, null, window.location.pathname);
        }, 3000);
    }
    if (urlParams.has('deleted') && urlParams.get('deleted') == '1') {
        // Display success message
        document.getElementById('success-message').style.display = 'block';

        // Hide the message after 3 seconds
        setTimeout(() => {
            document.getElementById('success-message').style.display = 'none';

            // Remove the query parameter from the URL
            history.replaceState(null, null, window.location.pathname);
        }, 3000);
    }
}
function closeSuccessMessage() {
    document.getElementById('success-message').style.display = 'none';
}
</script>

</body>
</html>
