<?php
session_start();
include __DIR__ . '/../db.php';
conn();
global $conns;

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$foods = [];
$groupedFoods = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $room_id = $_POST['room_id'];
    $room_name = $_POST['room_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];

    $orderSummary = "";
    $totalOverall = 0;

    foreach ($quantities as $food_id => $qty) {
        if ((int)$qty > 0) {
            $price = (float)$prices[$food_id];
            $total_price = $qty * $price;
            $totalOverall += $total_price;

            $stmt = $conns->prepare("SELECT name FROM resto_menu WHERE id = ?");
            $stmt->bind_param("i", $food_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $food = $result->fetch_assoc();
            $food_name = $food['name'] ?? 'Unknown';

            $orderSummary .= "{$food_name} x{$qty} ₱" . number_format($total_price, 2) . ", ";

            // Insert individual food order
            $insert = $conns->prepare("INSERT INTO ordered_foods (transaction_id, username, room_id, room_name, food_id, food_name, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("isisssid", $room_id, $username, $room_id, $room_name, $food_id, $food_name, $qty, $total_price);
            $insert->execute();
        }
    }

    // Final summary string with total
    $orderSummary .= "Total: ₱" . number_format($totalOverall, 2);

    // Save summary into separate table
    $summaryInsert = $conns->prepare("INSERT INTO order_receipts (username, room_id, room_name, summary, total) VALUES (?, ?, ?, ?, ?)");
    $summaryInsert->bind_param("sissd", $username, $room_id, $room_name, $orderSummary, $totalOverall);
    $summaryInsert->execute();

    echo "<script>alert('Order placed successfully!'); window.location.href='transaction_request.php';</script>";
    exit;
}

// Fetch foods
$result = $conns->query("SELECT * FROM resto_menu ORDER BY category, name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $groupedFoods[$row['category']][] = $row;
    }
}

// Fetch user's room info
$roomQuery = $conns->prepare("SELECT * FROM transactions WHERE username = ?");
$roomQuery->bind_param("s", $username);
$roomQuery->execute();
$roomResult = $roomQuery->get_result();
$roomData = $roomResult->fetch_assoc();

$room_name = $roomData['room_name'] ?? 'N/A';
$room_id = $roomData['room_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <title>Order Foods</title>
   <style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: #f0f2f5;
    }

    header {
        background: #222;
        color: white;
        padding: 15px;
        text-align: center;
    }

    .container {
        display: flex;
        padding: 20px;
        gap: 20px;
    }

    .menu-section {
        flex: 2;
        overflow-y: auto;
        max-height: 85vh;
    }

    .summary-section {
        flex: 1;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .category-title {
        font-size: 1.2em;
        margin: 20px 0 10px;
        font-weight: bold;
    }

    .menu-items {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

   .card {
    width: 280px; /* was 350px */
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    padding: 10px;
    flex-direction: column;
}

.card img {
    width: 100%;
    height: 220px; /* was 360px */
    object-fit: cover;
}


    .card-body {
        padding: 10px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-title {
        margin: 0;
        font-weight: bold;
        font-size: 1.1em;
    }

    .form-control {
        width: 100%;
        padding: 6px 10px;
        margin-top: 8px;
        font-size: 1em;
    }

    .summary-list {
        list-style: none;
        padding: 0;
    }

    .summary-list li {
        padding: 8px 0;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #ddd;
    }

    .total-box {
        font-weight: bold;
        text-align: right;
        margin-top: 15px;
        font-size: 1.2em;
    }

    .submit-btn {
        margin-top: 20px;
        width: 100%;
        padding: 10px;
        background: #28a745;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .submit-btn:hover {
        background: #218838;
    }

    .qty-btn {
        padding: 5px 10px;
        font-size: 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .qty-btn:hover {
        background-color: #0056b3;
    }a{
        color: #ffffffff;
        text-decoration: none;
    }
    </style>
</head>
<body>

<header>



    <h1>Rage Room & Resto</h1>
     <h1>
<a href="LandingPage.php" >Home </a>
     </h1>  

</header>

<div class="container">
    <!-- Left Column: Menu -->
    <div class="menu-section">
        <form method="POST" id="orderForm">
            <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
            <input type="hidden" name="room_id" value="<?= htmlspecialchars($room_id) ?>">
            <input type="hidden" name="room_name" value="<?= htmlspecialchars($room_name) ?>">

            <?php foreach ($groupedFoods as $category => $items): ?>
                <div class="category-title">🍽️ <?= htmlspecialchars($category) ?></div>
                <div class="menu-items">
                    <?php foreach ($items as $food): ?>
                        <div class="card">
                            <img src="../<?= htmlspecialchars($food['photo']) ?>" alt="Food">
                            <div class="card-body">
                                <div class="card-title"><?= htmlspecialchars($food['name']) ?></div>
                                <p style="margin: 5px 0; font-size: 0.9em;"><?= htmlspecialchars($food['description']) ?></p>
                                <p><strong>₱<?= number_format($food['price'], 2) ?></strong></p>

                                <!-- Quantity Buttons -->
                                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                    <button type="button" class="qty-btn" onclick="adjustQuantity(<?= $food['id'] ?>, -1)">➖</button>
                                    <span id="qty-display-<?= $food['id'] ?>">0</span>
                                    <button type="button" class="qty-btn" onclick="adjustQuantity(<?= $food['id'] ?>, 1)">➕</button>
                                </div>

                                <!-- Hidden Inputs -->
                                <input type="hidden" name="quantity[<?= $food['id'] ?>]" id="qty-input-<?= $food['id'] ?>" value="0">
                                <input type="hidden" name="price[<?= $food['id'] ?>]" value="<?= $food['price'] ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="total-box" id="grandTotal">Total: ₱0.00</div>
            <button type="submit" class="submit-btn">🧾 Place Order</button>
        </form>
    </div>

    <!-- Right Column: Summary -->
    <div class="summary-section">
        <h3>🛒 Order Summary</h3>
        <ul class="summary-list" id="summaryList"></ul>
        <div class="total-box" id="summaryTotal">₱0.00</div>
    </div>
</div>

<script>
function updateTotals() {
    const inputs = document.querySelectorAll("input[name^='quantity']");
    const summaryList = document.getElementById("summaryList");
    let grandTotal = 0;
    let summaryHTML = '';

    inputs.forEach(input => {
        const qty = parseInt(input.value);
        if (qty > 0) {
            const id = input.name.match(/\d+/)[0];
            const priceInput = document.querySelector(`input[name='price[${id}]']`);
            const price = parseFloat(priceInput.value);
            const name = input.closest('.card-body').querySelector('.card-title').innerText;
            const total = qty * price;
            grandTotal += total;
            summaryHTML += `<li>${name} x ${qty}<span>₱${total.toFixed(2)}</span></li>`;
        }
    });

    summaryList.innerHTML = summaryHTML || '<li>No items selected.</li>';
    document.getElementById("grandTotal").innerText = 'Total: ₱' + grandTotal.toFixed(2);
    document.getElementById("summaryTotal").innerText = '₱' + grandTotal.toFixed(2);
}

function adjustQuantity(foodId, delta) {
    const input = document.getElementById(`qty-input-${foodId}`);
    const display = document.getElementById(`qty-display-${foodId}`);
    let current = parseInt(input.value) || 0;
    current += delta;
    if (current < 0) current = 0;
    input.value = current;
    display.textContent = current;
    updateTotals();
}
</script>
</body>
</html>
