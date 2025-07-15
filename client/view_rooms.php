<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../LandingPage.php");
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "resto_rant_management_system";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_SESSION['username'];
    $phone_number = $_POST['phone_number'] ?? '';
    $room_id = $_POST['room_id'] ?? '';
    $address = $_POST['address'] ?? '';
    $date_to_avail = $_POST['date_to_avail'] ?? '';
    $_SESSION['address'] = $address;

    $stmt = $conn->prepare("SELECT name, price FROM rage_rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $room = $result->fetch_assoc()) {
        $room_name = $room['name'];
        $price = $room['price'];

        // Determine booking status
        $today = date('Y-m-d');
        $status = ($date_to_avail === $today) ? 'Approved' : 'Pending';

        $insert = $conn->prepare("INSERT INTO transactions (username, phone_number, room_id, room_name, price, date_to_avail, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssissss", $username, $phone_number, $room_id, $room_name, $price, $date_to_avail, $status);
        $insert->execute();

        if ($insert->affected_rows > 0) {
            // ✅ Mark room as Unavailable if booking is approved today
            if ($status === 'Approved') {
                $updateRoom = $conn->prepare("UPDATE rage_rooms SET status = 'Unavailable' WHERE id = ?");
                $updateRoom->bind_param("i", $room_id);
                $updateRoom->execute();
                $updateRoom->close();
            }
            echo "<script>alert('Booking successful!');</script>";
        } else {
            echo "<script>alert('Booking failed.');</script>";
        }
        $insert->close();
    } else {
        echo "<script>alert('Room not found.');</script>";
    }

    $stmt->close();
}

// Fetch Rooms
$sql = "SELECT * FROM rage_rooms WHERE status != 'Unavailable' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Rooms</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-title { font-size: 1.2rem; font-weight: 600; }
        .card-text { font-size: 0.95rem; }
        .modal-header { background-color: #0d6efd; color: white; }
        .card-footer button { width: 100%; }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
            color: white;
        }
        .status-available { background-color: #28a745; }
        .status-pending { background-color: #ffc107; }
        .status-unavailable { background-color: #dc3545; }
        .card {
            position: relative;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
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

<div class="container my-5">
    <h2 class="text-center mb-4">🎯 Available Rage Rooms</h2>
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($room = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <?php
                        $imgPath = !empty($room['image_path']) ? "../" . $room['image_path'] : "../uploads/default.jpg";
                        $statusClass = $room['status'] === 'Pending' ? 'status-pending' : ($room['status'] === 'Unavailable' ? 'status-unavailable' : 'status-available');
                        ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" class="card-img-top" alt="<?= htmlspecialchars($room['name']) ?>" style="height: 240px; object-fit: cover;">
                        <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($room['status']) ?></span>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($room['name']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($room['description'])) ?></p>
                            <p><strong>Type:</strong> <?= htmlspecialchars($room['room_type']) ?></p>
                            <p><strong>Props:</strong> <?= htmlspecialchars($room['props']) ?></p>
                            <p><strong>Price:</strong> ₱<?= number_format($room['price'], 2) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary book-btn" data-room-id="<?= $room['id'] ?>" data-auto-date="false">Book Now</button>
                                <button class="btn btn-success book-btn" data-room-id="<?= $room['id'] ?>" data-auto-date="true">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">No rooms available at the moment.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="view_rooms.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book a Room</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="room_id" id="modalRoomId">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?= $_SESSION['phone'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" required><?= $_SESSION['address'] ?? '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date to Avail</label>
                        <input type="date" name="date_to_avail" id="dateInput" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">✅ Confirm Booking</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.book-btn').forEach(button => {
    button.addEventListener('click', function () {
        const roomId = this.getAttribute('data-room-id');
        const autoDate = this.getAttribute('data-auto-date') === 'true';

        document.getElementById('modalRoomId').value = roomId;

        const dateInput = document.getElementById('dateInput');
        if (autoDate) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        } else {
            dateInput.value = '';
        }

        const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        bookingModal.show();
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>
