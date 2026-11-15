<?php  
session_start();
include 'db/dbcon.php';

if (!isset($_SESSION['auth_user']['id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

if (!isset($_GET['row_id'])) {
    header("Location: index.php");
    exit();
}

$row_id = $_GET['row_id'];

// ডাটাবেস থেকে বর্তমান ট্রানজেকশন তথ্য বের করা
$transaction_query = $con->prepare("SELECT id, date, amount, type, method, remark, people_id FROM transactions WHERE id = ? AND user_id = ?");
$transaction_query->bind_param("ii", $row_id, $user_id);
$transaction_query->execute();
$transaction = $transaction_query->get_result()->fetch_assoc();

if (!$transaction) {
    header("Location: index.php");
    exit();
}

// creditors টেবিল থেকে creditor এর নাম বের করা
$creditor_name = "Unknown"; // ডিফল্ট ভ্যালু
$creditor_query = $con->prepare("SELECT name FROM creditors WHERE id = ? AND user_id = ?");
$creditor_query->bind_param("ii", $transaction['people_id'], $user_id);
$creditor_query->execute();
$creditor_result = $creditor_query->get_result();
if ($creditor_row = $creditor_result->fetch_assoc()) {
    $creditor_name = $creditor_row['name'];
}

// ফর্ম সাবমিট হলে
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];

    // আগের balance বের করা
    $balance_query = $con->prepare("SELECT amount FROM creditors WHERE id = ? AND user_id = ?");
    $balance_query->bind_param("ii", $transaction['people_id'], $user_id);
    $balance_query->execute();
    $balance_result = $balance_query->get_result();
    $prev_balance = $balance_result->fetch_assoc()['amount'] ?? 0;

    // পার্থক্য বের করা
    $amount_difference = $amount - $transaction['amount'];

    // নতুন ব্যালেন্স হিসাব করা
    if ($type == 'given') {
        $new_balance = $prev_balance + $amount_difference;
    } else {
        $new_balance = $prev_balance - $amount_difference;
    }

    // ট্রানজেকশন আপডেট করা
    $stmt = $con->prepare("UPDATE transactions SET date = ?, type = ?, method = ?, amount = ?, remark = ? WHERE id = ?");
    $stmt->bind_param("sssdsi", $date, $type, $method, $amount, $remark, $row_id);
    $stmt->execute();

    // creditors টেবিলের balance আপডেট করা
    $update_creditor = $con->prepare("UPDATE creditors SET amount = ? WHERE id = ? AND user_id = ?");
    $update_creditor->bind_param("dii", $new_balance, $transaction['people_id'], $user_id);
    $update_creditor->execute();

    // পরবর্তী transactions গুলোর balance আপডেট করা
    $update_balance_query = $con->prepare("UPDATE transactions SET balance = balance + ? WHERE people_id = ? AND user_id = ? AND id > ?");
    $update_balance_query->bind_param("diii", $amount_difference, $transaction['people_id'], $user_id, $row_id);
    $update_balance_query->execute();

    header("Location: view_details.php?people_id=" . $transaction['people_id'] . "&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="css/edit_data.css">
</head>
<body>



<div class="container">
    <a id="backBtn" onclick="goBack()" href="view_details.php?people_id=<?php echo $transaction['people_id']; ?>">Back</a>
    <h2>Edit Transaction - <?php echo htmlspecialchars($creditor_name); ?></h2>
    
    <!-- Success Message -->
    <?php if (isset($_GET['updated'])): ?>
        <div id="success-message" class="success-message">
            ✅ Transaction Updated Successfully!
            <span class="close-btn" onclick="closeSuccessMessage()">❌</span>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="transaction-form">
            <div class="input-field">
                <input type="date" name="date" value="<?php echo htmlspecialchars($transaction['date']); ?>">
            </div>
            <div class="input-field">
                <select name="type">
                    <option value="given" <?php if ($transaction['type'] == 'given') echo 'selected'; ?>>Given</option>
                    <option value="taken" <?php if ($transaction['type'] == 'taken') echo 'selected'; ?>>Taken</option>
                </select>
            </div>
            <div class="input-field">
                <select name="method">
                    <option value="cash"  <?php if ($transaction['method'] == 'cash') echo 'selected'; ?>>Cash</option>
                    <option value="bkash" <?php if ($transaction['method'] == 'bkash') echo 'selected'; ?>>Bkash</option>
                    <option value="nogod" <?php if ($transaction['method'] == 'nogod') echo 'selected'; ?>>Nogod</option>
                    <option value="others"<?php if ($transaction['method'] == 'others') echo 'selected'; ?>>Others</option>
                </select>
            </div>
            <div class="input-field">
                <input type="number" name="amount" value="<?php echo htmlspecialchars($transaction['amount']); ?>" required placeholder="Enter amount">
            </div>
            
            <div class="input-field remark">
                <input type="text" name="remark" value="<?php echo htmlspecialchars($transaction['remark']); ?>" placeholder="Enter Note  that's not required">
            </div>
            
            <button type="submit" class="submit-btn">Update</button>
        </div>
    </form>
</div>

<script>
 function goBack() {
        window.history.back();
    }

function closeSuccessMessage() {
    document.getElementById('success-message').style.display = 'none';
}

// Success Message 3 সেকেন্ড পর অটো লুকানোর জন্য
setTimeout(() => {
    if (document.getElementById('success-message')) {
        closeSuccessMessage();
        history.replaceState(null, null, window.location.pathname); // URL থেকে updated=1 মুছে ফেলা
    }
}, 3000);
</script>

</body>
</html>
