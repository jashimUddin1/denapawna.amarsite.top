<!-- view_details.php -->
<?php
session_start();
include 'db/dbcon.php';

if (!isset($_SESSION['auth_user']['id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

if (!isset($_GET['people_id'])) {
    header("Location: index.php");
    exit();
}



$people_id = $_GET['people_id'];

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
$transactions_query = $con->prepare("SELECT id, date, type, method, amount, balance, remark FROM transactions WHERE people_id = ? ORDER BY id DESC");
$transactions_query->bind_param("i", $people_id);
$transactions_query->execute();
$transactions = $transactions_query->get_result();

// নতুন লেনদেন যোগ করা
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];

    // আগের ব্যালেন্স বের করা
    $balance_query = $con->prepare("SELECT balance FROM transactions WHERE people_id = ? ORDER BY id DESC LIMIT 1");
    $balance_query->bind_param("i", $people_id);
    $balance_query->execute();
    $result = $balance_query->get_result();
    $prev_balance = $result->fetch_assoc()['balance'] ?? 0;

    // নতুন ব্যালেন্স হিসাব করা
    $new_balance = ($type == 'given') ? $prev_balance + $amount : $prev_balance - $amount;

    // ডাটাবেজে ইনসার্ট করা
    $stmt = $con->prepare("INSERT INTO transactions (user_id, people_id, date, type, method, amount, balance, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssdis", $user_id, $people_id, $date, $type, $method, $amount, $new_balance, $remark);
    $stmt->execute();

     // creditors টেবিলের balance আপডেট করা
     $update_creditor = $con->prepare("UPDATE creditors SET amount = ? WHERE id = ? AND user_id = ?");
     $update_creditor->bind_param("dii", $new_balance, $people_id, $user_id);
     $update_creditor->execute();

    header("Location: view_details.php?people_id=" . $people_id);
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

<?php include 'includes/header.php' ?>

  <!-- Success Message -->
  <?php if (isset($_GET['success'])): ?>
        <div id="success-message" class="success-message">
            ✅ Transaction Updated Successfully!
            <span class="close-btn" onclick="closeSuccessMessage()">❌</span>
        </div>
  <?php endif; ?>

<div class="container">

    <table id="transactionTable" class="data-table">
        <thead>
            <tr>
                <th class="table-heading" colspan="7"><?php echo $people['name']; ?></th>
            </tr>
        </thead>
        <tr>
                <td colspan="7">
                <form method="post" class="">
                <div class="transaction-form">
                    <div class="input-field">
                        <input type="date" name="date">
                    </div>
                    <div class="input-field">
                        <select name="type">
                            <option value="given">Given</option>
                            <option value="taken">Taken</option>
                        </select>
                    </div>
                    <div class="input-field">
                        <select name="method">
                            <option value="cash">Cash</option>
                            <option value="bkash">Bkash</option>
                            <option value="nogod">Nogod</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="input-field">
                        <input type="number" name="amount" required placeholder="Enter amount">
                    </div>

                    <div class="input-field">
                        <input type="text" name="remark" placeholder="Enter Note">
                    </div>
                    
                    <button type="submit" class="submit-btn">Submit</button>
                </div>
                </form>
                </td>
            </tr>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Remark</th>
                <th>Action</th>
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
                <td>
                    <a href="edit_data.php?row_id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                    <a onclick="return confirm('are you sure?')" href="delete_data.php?row_id=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal for adding new creditor -->
<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Add New Person</h2>
    <form class="form-container" method="post" id="addPersonForm">
        <input type="text" name="name" required placeholder="Full Name">
        <input type="text" name="contact" placeholder="Type (e.g., Friend, Bank, Business)">
        <button type="submit">Add</button>
    </form>
  </div>
</div>

<script>
  // Get the modal and buttons
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("addNewBtn");
    var span = document.getElementsByClassName("close")[0];

    // Open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Handle form submission via AJAX
    document.getElementById("addPersonForm").onsubmit = function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "add_person.php", true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                alert('Person added successfully');
                modal.style.display = "none";
                window.location.reload();  // Reload the page to see the new person
            } else {
                alert('Something went wrong');
            }
        };
        xhr.send(formData);
    };
    
document.addEventListener("DOMContentLoaded", function () {
    let table = document.getElementById("transactionTable"); // টেবিলের ID সেট করো

    if (table) {
        let rows = table.getElementsByTagName("tr");
        let balance = 0;

        for (let i = rows.length - 1; i > 0; i--) { // উল্টোভাবে লুপ চালাবো, যেন ব্যালেন্স ঠিক থাকে
            let cells = rows[i].getElementsByTagName("td");

            if (cells.length > 0) {
                let amount = parseFloat(cells[3].innerText.replace(/,/g, "")) || 0; // Amount কলামের ডাটা
                let type = cells[1].innerText.trim(); // Type কলাম

                if (type === "given") {
                    balance += amount; // Given হলে balance বাড়বে
                } else if (type === "taken") {
                    balance -= amount; // Taken হলে balance কমবে
                }

                cells[4].innerText = balance.toLocaleString(); // Balance আপডেট করা হচ্ছে
            }
        }
    }
});

// Success Message দেখানোর জন্য
window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            document.getElementById('success-message').style.display = 'block';

            // 3 সেকেন্ড পর মেসেজ অটোমেটিকলি হাইড হবে
            setTimeout(() => {
                document.getElementById('success-message').style.display = 'none';
                history.replaceState(null, null, window.location.pathname); // URL থেকে success মুছে ফেলা
            }, 3000);
        }
    }
    function closeSuccessMessage() {
    document.getElementById('success-message').style.display = 'none';
    }
</script>
</body>
</html>
