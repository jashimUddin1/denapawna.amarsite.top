


<!-- people.php -->
<?php
session_start();
include 'db/dbcon.php';

// URL থেকে প্যারামিটার নেওয়া
$user_id = $_GET['user_id'] ?? null;
$people_id = $_GET['people_id'] ?? null;
$passkey = $_GET['passkey'] ?? null;

// ইনপুট চেক করা
if (!$user_id || !$people_id || !$passkey) {
    die("Access Denied! Invalid parameters.");
}

// ডাটাবেজ থেকে `passkey` যাচাই করা
$passkey_query = $con->prepare("SELECT passkey FROM creditors WHERE id = ? AND user_id = ?");
$passkey_query->bind_param("ii", $people_id, $user_id);
$passkey_query->execute();
$passkey_result = $passkey_query->get_result();
$passkey_data = $passkey_result->fetch_assoc();

if (!$passkey_data || $passkey_data['passkey'] !== $passkey) {
    die("Access Denied! Invalid passkey.");
}


// ব্যক্তির তথ্য বের করা
$people_query = $con->prepare("SELECT name FROM creditors WHERE id = ? AND user_id = ?");
$people_query->bind_param("ii", $people_id, $user_id);
$people_query->execute();
$people_result = $people_query->get_result();
$people = $people_result->fetch_assoc();

if (!$people) {
    header("Location: index.php");
    exit();
}

// লেনদেনের তথ্য বের করা
$transactions_query = $con->prepare("SELECT id, date, type, method, amount, balance, remark FROM transactions WHERE people_id = ? ORDER BY id ASC");
$transactions_query->bind_param("i", $people_id);
$transactions_query->execute();
$transactions = $transactions_query->get_result();

// নতুন লেনদেন যোগ করা
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $amount = $_POST['amount'];

    // আগের ব্যালেন্স বের করা
    $balance_query = $con->prepare("SELECT balance FROM transactions WHERE people_id = ? ORDER BY id DESC LIMIT 1");
    $balance_query->bind_param("i", $people_id);
    $balance_query->execute();
    $result = $balance_query->get_result();
    $prev_balance = $result->fetch_assoc()['balance'] ?? 0;

    // নতুন ব্যালেন্স হিসাব করা
    $new_balance = ($type == 'given') ? $prev_balance + $amount : $prev_balance - $amount;

    // ডাটাবেজে ইনসার্ট করা
    $stmt = $con->prepare("INSERT INTO transactions (user_id, people_id, date, type, method, amount, balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssdi", $user_id, $people_id, $date, $type, $method, $amount, $new_balance);
    $stmt->execute();

     // creditors টেবিলের balance আপডেট করা
     $update_creditor = $con->prepare("UPDATE creditors SET amount = ? WHERE id = ? AND user_id = ?");
     $update_creditor->bind_param("dii", $new_balance, $people_id, $user_id);
     $update_creditor->execute();

    header("Location: people.php?user_id=$user_id&people_id=$people_id");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $people['name']; ?> - Details</title>
    <link rel="stylesheet" href="css/view_details.css">
</head>
<body>

<div class="container">

    <table class="data-table">
        <thead>
            <tr>
                <th class="table-heading" colspan="6"><?php echo $people['name']; ?></th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Remark</th>
            </tr>
        </thead>
        
        <tbody>
            <?php while ($row = $transactions->fetch_assoc()) { ?>
            <tr>
                <td>
                    <?php 
                    $date = new DateTime($row['date']);
                    $formattedDate = $date->format('d-m-Y'); 
                    echo ($formattedDate == "30-11--0001") ? "Not Sure" : $formattedDate;
                    ?>
                </td>
                <td><?php echo $row['type']; ?></td>
                <td><?php echo $row['method']; ?></td>
                <td><?php echo number_format($row['amount']); ?></td>
                <td><?php echo number_format($row['balance']); ?></td>
                <td><?php echo $row['remark']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    
</script>
</body>
</html>
