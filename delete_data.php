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

// ট্রানজেকশনের তথ্য বের করা
$transaction_query = $con->prepare("SELECT id, amount, type, people_id FROM transactions WHERE id = ? AND user_id = ?");
$transaction_query->bind_param("ii", $row_id, $user_id);
$transaction_query->execute();
$transaction = $transaction_query->get_result()->fetch_assoc();

// যদি ট্রানজেকশন না পাওয়া যায়
if (!$transaction) {
    header("Location: index.php");
    exit();
}

// পূর্বের ব্যালেন্স বের করা
$people_id = $transaction['people_id'];
$balance_query = $con->prepare("SELECT amount FROM creditors WHERE id = ? AND user_id = ?");
$balance_query->bind_param("ii", $people_id, $user_id);
$balance_query->execute();
$balance_result = $balance_query->get_result();
$prev_balance = $balance_result->fetch_assoc()['amount'] ?? 0;

// ট্রানজেকশন ডিলিট করা
$delete_transaction = $con->prepare("DELETE FROM transactions WHERE id = ?");
$delete_transaction->bind_param("i", $row_id);
$delete_transaction->execute();

// ব্যালেন্স আপডেট করা (যদি 'given' হয়, ব্যালেন্স কমানো হবে, 'taken' হলে ব্যালেন্স বাড়ানো হবে)
if ($transaction['type'] == 'given') {
    $new_balance = $prev_balance - $transaction['amount'];
} else {
    $new_balance = $prev_balance + $transaction['amount'];
}

// creditors টেবিলের ব্যালেন্স আপডেট করা
$update_creditor = $con->prepare("UPDATE creditors SET amount = ? WHERE id = ? AND user_id = ?");
$update_creditor->bind_param("dii", $new_balance, $people_id, $user_id);
$update_creditor->execute();

// পরবর্তী ট্রানজেকশনের ব্যালেন্স আপডেট করা
$update_balance_query = $con->prepare("UPDATE transactions SET balance = ? WHERE people_id = ? AND user_id = ? AND id > ? ORDER BY id ASC");
$update_balance_query->bind_param("diii", $new_balance, $people_id, $user_id, $row_id);
$update_balance_query->execute();

header("Location: view_details.php?people_id=" . $people_id);
exit();
?>
