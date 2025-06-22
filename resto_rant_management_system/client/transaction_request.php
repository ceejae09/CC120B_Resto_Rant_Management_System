<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../LandingPage.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user'; // Default to 'user'

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

// Fetch user transactions
$txn_stmt = $conn->prepare("SELECT * FROM transactions WHERE username = ? ORDER BY created_at DESC");
$txn_stmt->bind_param("s", $username);
$txn_stmt->execute();
$txn_result = $txn_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Booking Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
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
        </div>

        <!-- Right Notifications Section -->
        <div class="col-md-9">
            <h3 class="mb-4 text-center">📩 Your Booking Notifications</h3>

            <?php if ($txn_result->num_rows > 0): ?>
                <ul class="list-group">
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
                <div class="alert alert-info text-center">You have no booking notifications yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$user_stmt->close();
$txn_stmt->close();
$conn->close();
?>
