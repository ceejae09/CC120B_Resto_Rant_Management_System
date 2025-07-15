<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../LandingPage.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user';

$conn = new mysqli("localhost", "root", "", "resto_rant_management_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch user transactions (room bookings)
$txn_stmt = $conn->prepare("SELECT * FROM transactions WHERE username = ? ORDER BY created_at DESC");
$txn_stmt->bind_param("s", $username);
$txn_stmt->execute();
$txn_result = $txn_stmt->get_result();

// Fetch user food orders
$order_stmt = $conn->prepare("SELECT * FROM order_receipts WHERE username = ? ORDER BY created_at DESC");
$order_stmt->bind_param("s", $username);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Booking Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .divider {
            border-right: 1px solid #ccc;
            height: 100%;
        }
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
                    <li class="nav-item"><a class="nav-link" href="LandingPage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="order_foods.php">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_rooms.php">Room</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content Area -->
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3 divider">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">👤 User Information</div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($user_data['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user_data['phone']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($user_data['address']) ?></p>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">📩 Notifications</div>
                <div class="card-body">
          <button  class="btn btn-primary mb-3">
                   <a href="transaction_request.php">
                    view
                   </a> 
          </button>
            <!-- Room Booking Transactions -->
            <?php if ($txn_result->num_rows > 0): ?>
                <ul class="list-group mb-4">
                    <?php while ($row = $txn_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($row['room_name']) ?> — ₱<?= number_format($row['price'], 2) ?></div>
                                Date to Avail: <strong><?= htmlspecialchars($row['date_to_avail']) ?></strong><br>
                                Status:
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($row['status'] === 'Approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                                <div class="text-muted small">Requested on <?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-info text-center">You have no room booking notifications yet.</div>
            <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Notifications Section -->
        <div class="col-md-9">
            <!-- Food Orders -->
            <h3 class="mb-4 text-center">🍽️ Your Food Orders</h3>
            <?php if ($order_result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($order = $order_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">
                                    Order #<?= $order['id'] ?> — ₱<?= number_format($order['total'], 2) ?>
                                    <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#summaryModal<?= $order['id'] ?>">View</button>
                                </div>
                                Ordered on: <?= date("F j, Y, g:i a", strtotime($order['created_at'])) ?><br>
                                Status:
                                <?php if ($order['status'] === 'Done'): ?>
                                    <span class="badge bg-success">Served</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($order['status']) ?></span>
                                <?php endif; ?>
                            </div>
                        </li>

                        <!-- Modal for Summary -->
                        <div class="modal fade" id="summaryModal<?= $order['id'] ?>" tabindex="-1" aria-labelledby="summaryLabel<?= $order['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="summaryLabel<?= $order['id'] ?>">📝 Order Summary (Order #<?= $order['id'] ?>)</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?= nl2br(htmlspecialchars($order['summary'])) ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-info text-center">You have no food order notifications yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<style>
          a{
                    color: #ffffff;
                    text-decoration: none;
          }
    </style>

<?php
$user_stmt->close();
$txn_stmt->close();
$order_stmt->close();
$conn->close();
?>
