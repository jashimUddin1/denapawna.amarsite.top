<?php
session_start();
include 'db/dbcon.php';

// টাইমজোন সেট করা
date_default_timezone_set('Asia/Dhaka');
// বর্তমান সময় প্রদর্শন করা
$actualTime = date('Y-m-d H:i:s');

// 🛠️ Debugging Mode চালু
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['auth_user']['id'])) {
    die("Unauthorized access!");
}

$user_id = $_SESSION['auth_user']['id'];

if (isset($_GET['people_id'])) {
    $people_id = intval($_GET['people_id']);

    // ✅ creditors থেকে ডাটা নেওয়া
    $query = $con->prepare("SELECT * FROM creditors WHERE id = ? AND user_id = ?");
    if (!$query) {
        die("SQL Prepare Failed: " . $con->error);
    }
    $query->bind_param("ii", $people_id, $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ✅ tash_people টেবিলে সংরক্ষণ (type বাদ দিয়ে)
        $insert_query = $con->prepare("INSERT INTO tash_people (id, user_id, name, contact, amount, deleted_at) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$insert_query) {
            die("Insert Query Failed: " . $con->error);
        }
        $insert_query->bind_param("iissds", $row['id'], $user_id, $row['name'], $row['contact'], $row['amount'], $actualTime);
        $insert_query->execute();

        // ✅ tash_transaction টেবিলে ডাটা ইনসার্ট করা
        $transaction_query = $con->prepare("SELECT * FROM transactions WHERE people_id = ?");
        if (!$transaction_query) {
            die("Transaction Select Query Failed: " . $con->error);
        }
        $transaction_query->bind_param("i", $people_id);
        $transaction_query->execute();
        $transaction_result = $transaction_query->get_result();

        // Insert each transaction into tash_transaction
        while ($transaction_row = $transaction_result->fetch_assoc()) {
            $insert_transaction = $con->prepare("INSERT INTO tash_transactions
            (user_id, people_id, date, type, method, amount, balance, Remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$insert_transaction) {
                die("Transaction Insert Query Failed: " . $con->error);
            }
            $insert_transaction->bind_param("iisssdss", $user_id, $people_id, $transaction_row['date'], $transaction_row['type'], $transaction_row['method'], $transaction_row['amount'], $transaction_row['balance'], $transaction_row['Remark']);
            $insert_transaction->execute();
        }

        // ✅ প্রথমে transactions টেবিল থেকে এই people_id সম্পর্কিত ডাটা মুছতে হবে
        $delete_transactions = $con->prepare("DELETE FROM transactions WHERE people_id = ?");
        if (!$delete_transactions) {
            die("Transaction Delete Query Failed: " . $con->error);
        }
        $delete_transactions->bind_param("i", $people_id);
        $delete_transactions->execute();

        // ✅ creditors থেকে ডিলিট করা
        $delete_query = $con->prepare("DELETE FROM creditors WHERE id = ? AND user_id = ?");
        if (!$delete_query) {
            die("Delete Query Failed: " . $con->error);
        }
        $delete_query->bind_param("ii", $people_id, $user_id);
        $delete_query->execute();

        header("Location: index.php?deleted=1");
        exit();
    } else {
        $_SESSION['message']['warning'] = "Data Not Found!";
        header("Location: index.php");
    }
} else {
    die("Invalid Request!");
}
?>
