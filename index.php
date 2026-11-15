<!-- index.php -->
<?php
session_start();
include 'db/dbcon.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['auth_user']['id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

// Dena & Pawna এর মোট হিসাব
$total_dena_query = $con->query("SELECT SUM(amount) AS total_dena FROM creditors WHERE user_id = $user_id AND amount < 0");
$total_pawna_query = $con->query("SELECT SUM(amount) AS total_pawna FROM creditors WHERE user_id = $user_id AND amount > 0");

$total_dena = $total_dena_query->fetch_assoc()['total_dena'] ?? 0;
$total_pawna = $total_pawna_query->fetch_assoc()['total_pawna'] ?? 0;
$total_balance = $total_pawna + $total_dena;

// সমস্ত creditors লোড করা
$creditors_dena = $con->query("SELECT id, name, amount FROM creditors WHERE user_id = $user_id AND amount < 0 ORDER BY amount ASC");
$creditors_pawna = $con->query("SELECT id, name, amount FROM creditors WHERE user_id = $user_id AND amount > 0 ORDER BY amount DESC");
$creditors_finished = $con->query("SELECT id, name, amount FROM creditors WHERE user_id = $user_id AND amount = 0 ORDER BY update_at DESC");
$sn = 1;
$psn = 1;
$fsn = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debt Management</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .search-bar-wrapper {
            display: flex;
            justify-content: flex-end;
            margin: 15px auto 10px auto;
            width: 95%;
            max-width: 1200px;
        }
        #creditorSearchInput {
            padding: 8px 14px;
            width: 280px;
            max-width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        #creditorSearchInput:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body>
   <?php include 'includes/header.php'; ?> 
   
<h2></h2>

<div id="success-message" class="success-message">
    ✅ Passkey updated successfully!
    <span class="close-btn" onclick="closeSuccessMessage()">❌</span>
</div>

<div class="balancesheet">
    <table>
        <tr>
            <td colspan="4">Balancesheet List</td>
        </tr>
        <tr>
            <th>Last Update</th>
            <th>Total Pawna</th>
            <th>Total Dena</th>
            <th>Total Balance</th>
        </tr>
        <tr>
            <td><?php echo date("d/m/Y"); ?></td>
            <td style="color: green;"><?php echo number_format($total_pawna); ?></td>
            <td style="color: red;"><?php echo number_format($total_dena); ?></td>
            <td style="color: <?php echo ($total_balance > 0) ? 'green' : (($total_balance < 0) ? 'red' : 'black'); ?>;">
                <?php echo number_format($total_balance); ?>
            </td>
        </tr>
    </table>
</div>

<!-- 🔍 Live Search Bar -->
<div class="search-bar-wrapper">
    <input type="text" id="creditorSearchInput" onkeyup="searchCreditorTables()" placeholder="Search name, type, amount...">
</div>

<!-- Dena List -->
<div class="denaList">
    <table>
        <tr>
            <td colspan="6">Dena List</td>
        </tr>
        <tr>
            <th>S.N</th>
            <th>Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
        
        <?php 
        $sn = 1;
        while ($row = $creditors_dena->fetch_assoc()) { 
            $query = "SELECT passkey FROM creditors WHERE user_id = ? AND id = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ii", $user_id, $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $passkey = "";
            if ($result->num_rows > 0) {
                $row_data = $result->fetch_assoc();
                $passkey = $row_data['passkey'];
            }
        ?>
        <tr>
            <td><?php echo $sn++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td style="color: red;">Dena</td>
            <td style="color: red;"><?php echo number_format($row['amount']); ?></td>
            <td>
                <button class="btn btn-primary copyBtn" 
                    data-link="people.php?user_id=<?php echo $user_id; ?>&people_id=<?php echo $row['id']; ?>&passkey=<?php echo $passkey; ?>">
                    Copy Link
                </button>
                <a href="view_details.php?people_id=<?php echo $row['id']; ?>" class="btn btn-info">View Details</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Pawna List -->
<div class="pawnaList">
    <table>
        <tr>
            <td colspan="6">Pawna List</td>
        </tr>
        <tr>
            <th>S.N</th>
            <th>Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
        
        <?php 
        $psn = 1;
        while ($row = $creditors_pawna->fetch_assoc()) { 
            $query = "SELECT passkey FROM creditors WHERE user_id = ? AND id = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ii", $user_id, $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $passkey = "";
            if ($result->num_rows > 0) {
                $row_data = $result->fetch_assoc();
                $passkey = $row_data['passkey'];
            }
        ?>
        <tr>
            <td><?php echo $psn++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td style="color: green;">Pawna</td>
            <td style="color: green;"><?php echo number_format($row['amount']); ?></td>
            <td>
                <button class="btn btn-primary copyBtn" 
                    data-link="people.php?user_id=<?php echo $user_id; ?>&people_id=<?php echo $row['id']; ?>&passkey=<?php echo $passkey; ?>">
                    Copy Link
                </button>
                <a href="view_details.php?people_id=<?php echo $row['id']; ?>" class="btn btn-info">View Details</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Finished List -->
<div class="finishedList">
    <table>
        <tr>
            <td colspan="6">Finished List</td>
        </tr>
        <tr>
            <th>S.N</th>
            <th>Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
        
        <?php 
        $fsn = 1;
        while ($row = $creditors_finished->fetch_assoc()) { 
            $query = "SELECT passkey FROM creditors WHERE user_id = ? AND id = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ii", $user_id, $row['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $passkey = "";
            if ($result->num_rows > 0) {
                $row_data = $result->fetch_assoc();
                $passkey = $row_data['passkey'];
            }
        ?>
        <tr>
            <td><?php echo $fsn++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td style="color: gray;">Finished</td>
            <td style="color: gray;"><?php echo number_format($row['amount']); ?></td>
            <td>
                <button class="btn btn-primary copyBtn" 
                    data-link="people.php?user_id=<?php echo $user_id; ?>&people_id=<?php echo $row['id']; ?>&passkey=<?php echo $passkey; ?>">
                    Copy Link
                </button>
                <a href="view_details.php?people_id=<?php echo $row['id']; ?>" class="btn btn-info">View Details</a>
            </td>
        </tr>
        <?php } ?>
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
    // 🔍 Live Search Function (Dena, Pawna, Finished টেবিলের জন্য)
    function searchCreditorTables() {
        const input = document.getElementById('creditorSearchInput');
        const filter = input.value.toLowerCase().trim();
        const tables = document.querySelectorAll('.denaList table, .pawnaList table, .finishedList table');

        tables.forEach(table => {
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                // টেবিলের টাইটেল ও হেডার রো বাদ দিয়ে শুধু ডাটা রো ফিল্টার হবে
                if (row.querySelector('th') || row.querySelector('td[colspan]')) {
                    return;
                }
                const cells = row.querySelectorAll('td');
                // S.N, Name, Type, Amount কলামের টেক্সট চেক করা হচ্ছে
                const searchableText = [
                    cells[0]?.innerText || '',
                    cells[1]?.innerText || '',
                    cells[2]?.innerText || '',
                    cells[3]?.innerText || ''
                ].join(' ').toLowerCase();

                row.style.display = searchableText.includes(filter) ? '' : 'none';
            });
        });
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

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });

    // Handle form submission via AJAX
    document.getElementById("addPersonForm").onsubmit = function(event) {
        event.preventDefault();
        /* eslint-env browser */

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
    
    document.addEventListener("DOMContentLoaded", function() {
        const copyButtons = document.querySelectorAll(".copyBtn");

        copyButtons.forEach(button => {
            button.addEventListener("click", function() {
                let link = button.getAttribute("data-link");
                let fullLink = window.location.origin + "/" + link;

                navigator.clipboard.writeText(fullLink).then(() => {
                    alert("Link copied: " + fullLink);
                }).catch(err => {
                    console.error("Could not copy link: ", err);
                });
            });
        });
    });

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