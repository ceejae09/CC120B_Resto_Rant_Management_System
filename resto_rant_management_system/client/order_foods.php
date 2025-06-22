<?php
session_start();
include '../db.php';
conn();
global $conns;

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$foods = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $room_id = $_POST['room_id'];
    $room_name = $_POST['room_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];

    foreach ($quantities as $food_id => $qty) {
        if ((int)$qty > 0) {
            $price = (float)$prices[$food_id];
            $total_price = $qty * $price;

            $stmt = $conns->prepare("SELECT name FROM resto_menu WHERE id = ?");
            $stmt->bind_param("i", $food_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $food = $result->fetch_assoc();
            $food_name = $food['name'] ?? 'Unknown';

            $insert = $conns->prepare("INSERT INTO ordered_foods (transaction_id, username, room_id, room_name, food_id, food_name, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("isisssid", $room_id, $username, $room_id, $room_name, $food_id, $food_name, $qty, $total_price);
            $insert->execute();
        }
    }
    echo "<script>alert('Order placed successfully!'); window.location.href='transaction_request.php';</script>";
    exit;
}

// Fetch foods
$result = $conns->query("SELECT * FROM resto_menu ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $foods[] = $row;
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
  <title>Order Foods</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
    h2 { margin: 40px 0 20px; text-align: center; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 12px 15px; border: 1px solid #ccc; text-align: center; }
    th { background-color: #333; color: white; }
    input[type="number"] { width: 60px; padding: 4px; }
    .total-container { margin-top: 20px; font-size: 18px; text-align: right; }
    .submit-btn { margin-top: 20px; padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; }
    img.thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../LandingPage.php">Rage Room & Resto</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">📞 0927-743-3290</a></li>
        
          
          
                <?php if (isset($_SESSION['username'])): ?>
                          <li class="nav-item"><a class="nav-link" href="transaction_request.php">Transaction</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $role === 'admin' ? '../admin/Home.php' : 'LandingPage.php' ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
  <h2>Order Foods</h2>

  <form id="orderForm" method="POST">
    <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($room_id) ?>">
    <input type="hidden" name="room_name" value="<?= htmlspecialchars($room_name) ?>">

    <table>
      <thead>
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Description</th>
          <th>Category</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($foods as $food): ?>
          <tr data-price="<?= $food['price'] ?>" data-id="<?= $food['id'] ?>" data-name="<?= htmlspecialchars($food['name']) ?>">
            <td>
              <?php
              $photoPath = "../" . $food['photo'];
              if (!empty($food['photo']) && file_exists($photoPath)):
              ?>
                <img src="<?= htmlspecialchars($photoPath) ?>" class="thumb" alt="Food Photo">
              <?php else: ?>
                <img src="../assets/no-image.png" class="thumb" alt="No image available">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($food['name']) ?></td>
            <td><?= htmlspecialchars($food['description']) ?></td>
            <td><?= htmlspecialchars($food['category']) ?></td>
            <td>₱<?= number_format($food['price'], 2) ?></td>
            <td>
              <input type="number" name="quantity[<?= $food['id'] ?>]" min="0" value="0" onchange="updateTotals()">
              <input type="hidden" name="price[<?= $food['id'] ?>]" value="<?= $food['price'] ?>">
            </td>
            <td class="item-total">₱0.00</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="total-container">
      <strong>Grand Total: <span id="grandTotal">₱0.00</span></strong>
    </div>

    <button type="submit" class="submit-btn">Place Order</button>
  </form>
</div>

<!-- Modal -->
<div class="modal fade" id="orderSummaryModal" tabindex="-1" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🧾 Confirm Your Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orderSummaryBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit Order</button>
        <button type="button" class="btn btn-success" id="confirmOrderBtn">Confirm Order</button>
      </div>
    </div>
  </div>
</div>

<script>
  function updateTotals() {
    const rows = document.querySelectorAll("tbody tr");
    let grandTotal = 0;

    rows.forEach(row => {
      const price = parseFloat(row.dataset.price);
      const qtyInput = row.querySelector("input[name^='quantity']");
      const totalCell = row.querySelector(".item-total");

      const qty = parseInt(qtyInput.value) || 0;
      const itemTotal = price * qty;
      totalCell.textContent = '₱' + itemTotal.toFixed(2);
      grandTotal += itemTotal;
    });

    document.getElementById("grandTotal").textContent = '₱' + grandTotal.toFixed(2);
  }

  const form = document.getElementById("orderForm");
  const modal = new bootstrap.Modal(document.getElementById("orderSummaryModal"));

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const username = "<?= $username ?>";
    const room = "<?= $room_name ?>";
    let total = 0;
    let hasOrder = false;

    let summary = `
      <p><strong>👤 User:</strong> ${username}<br>
      <strong>🛏️ Room:</strong> ${room}</p>
      <h5>🍽️ Order Summary:</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
    `;

    const rows = document.querySelectorAll("tbody tr");
    rows.forEach(row => {
      const qtyInput = row.querySelector("input[name^='quantity']");
      const qty = parseInt(qtyInput.value);
      if (qty > 0) {
        hasOrder = true;
        const name = row.dataset.name;
        const price = parseFloat(row.dataset.price);
        const subtotal = qty * price;
        total += subtotal;

        summary += `
          <tr>
            <td>${name}</td>
            <td>${qty}</td>
            <td>₱${subtotal.toFixed(2)}</td>
          </tr>
        `;
      }
    });

    if (!hasOrder) {
      alert("⚠️ Please select at least one food item.");
      return;
    }

    summary += `
        </tbody>
      </table>
      <p><strong>💰 Total Payment:</strong> ₱${total.toFixed(2)}</p>
    `;

    document.getElementById("orderSummaryBody").innerHTML = summary;
    modal.show();
  });

  document.getElementById("confirmOrderBtn").addEventListener("click", () => {
    modal.hide();
    form.submit();
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
