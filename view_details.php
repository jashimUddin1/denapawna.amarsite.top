<!-- view_details.php -->
<?php
session_start();
include 'db/dbcon.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($people['name']); ?> - Details</title>
    <link rel="stylesheet" href="css/view_details.css">
    <style>
        .remark-short {
            cursor: pointer;
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }
        .remark-short:hover {
            color: #1d4ed8;
        }
        /* Remark Popup Modal */
        #remarkModal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.55);
            align-items: center;
            justify-content: center;
        }
        .remark-modal-box {
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 10px;
            width: 90%;
            max-width: 460px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: fadeInScale 0.2s ease;
        }
        .remark-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .remark-modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #1e293b;
        }
        .remark-close-btn {
            font-size: 24px;
            font-weight: bold;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
        }
        .remark-close-btn:hover {
            color: #0f172a;
        }
        #fullRemarkText {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

<?php include 'includes/view_header.php' ?>

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
                <th class="table-heading" colspan="7"><?php echo htmlspecialchars($people['name']); ?></th>
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
                                <option value="Cash">Cash</option>
                                <option value="Bkash">Bkash</option>
                                <option value="Nogod">Nogod</option>
                                <option value="Rocket">Rocket</option>
                                <option value="City Bank">City Bank</option>
                                <option value="Others">Others</option>
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
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['method']); ?></td>
                <td><?php echo number_format($row['amount']); ?></td>
                <td><?php echo number_format($row['balance']); ?></td>
                <td>
                    <?php 
                    $remark = $row['remark'] ?? '';
                    $remarkLen = function_exists('mb_strlen') ? mb_strlen($remark, 'UTF-8') : strlen($remark);

                    if ($remarkLen > 20) {
                        $shortRemark = function_exists('mb_substr') ? mb_substr($remark, 0, 20, 'UTF-8') : substr($remark, 0, 20);
                        echo '<span class="remark-short" title="Click to view full remark" data-remark="' . htmlspecialchars($remark, ENT_QUOTES, 'UTF-8') . '" onclick="openRemarkModal(this)">'
                            . htmlspecialchars($shortRemark, ENT_QUOTES, 'UTF-8') . '...'
                            . '</span>';
                    } else {
                        echo htmlspecialchars($remark, ENT_QUOTES, 'UTF-8');
                    }
                    ?>
                </td>
                <td>
                    <a href="edit_data.php?row_id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                    <a onclick="return confirm('are you sure?')" href="delete_data.php?row_id=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Remark Popup Modal -->
<div id="remarkModal">
    <div class="remark-modal-box">
        <div class="remark-modal-header">
            <h3>Remark Details</h3>
            <span class="remark-close-btn" onclick="closeRemarkModal()">&times;</span>
        </div>
        <p id="fullRemarkText"></p>
    </div>
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
    // Remark Popup Functions
    function openRemarkModal(el) {
        const fullText = el.getAttribute('data-remark');
        document.getElementById('fullRemarkText').textContent = fullText;
        document.getElementById('remarkModal').style.display = 'flex';
    }

    function closeRemarkModal() {
        document.getElementById('remarkModal').style.display = 'none';
        document.getElementById('fullRemarkText').textContent = '';
    }

    // Get the modal and buttons
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("addNewBtn");
    var span = document.getElementsByClassName("close")[0];

    if (btn) {
        btn.onclick = function() {
            modal.style.display = "block";
        }
    }

    if (span) {
        span.onclick = function() {
            modal.style.display = "none";
        }
    }

    // যেকোনো মডালের বাইরে ক্লিক করলে বন্ধ হবে
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        var remarkModal = document.getElementById("remarkModal");
        if (event.target == remarkModal) {
            closeRemarkModal();
        }
    });

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
                window.location.reload();
            } else {
                alert('Something went wrong');
            }
        };
        xhr.send(formData);
    };
    
    document.addEventListener("DOMContentLoaded", function () {
        let table = document.getElementById("transactionTable");

        if (table) {
            let rows = table.getElementsByTagName("tr");
            let balance = 0;

            for (let i = rows.length - 1; i > 0; i--) {
                let cells = rows[i].getElementsByTagName("td");

                if (cells.length > 0) {
                    let amount = parseFloat(cells[3].innerText.replace(/,/g, "")) || 0;
                    let type = cells[1].innerText.trim();

                    if (type === "given") {
                        balance += amount;
                    } else if (type === "taken") {
                        balance -= amount;
                    }

                    cells[4].innerText = balance.toLocaleString();
                }
            }
        }
    });

    // Success Message দেখানোর জন্য
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            document.getElementById('success-message').style.display = 'block';

            setTimeout(() => {
                document.getElementById('success-message').style.display = 'none';
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