<?php
include 'db.php';
conn(); // initialize database connection
global $conns;

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'], $_POST['action'])) {
    $id = intval($_POST['order_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conns->prepare("UPDATE order_receipts SET status = 'done' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Order #$id approved.";
                } else {
                    $error = "Approval failed: Order ID not found or already approved.";
                }
            } else {
                $error = "Approval failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "SQL Error (Approve): " . $conns->error;
        }

    } elseif ($action === 'reject' || $action === 'cancel') {
        $stmt = $conns->prepare("DELETE FROM order_receipts WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Order #$id " . ($action === 'reject' ? "rejected" : "canceled") . " and removed.";
                } else {
                    $error = ucfirst($action) . " failed: Order not found.";
                }
            } else {
                $error = ucfirst($action) . " failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "SQL Error ($action): " . $conns->error;
        }

    } elseif ($action === 'done') {
        $conns->begin_transaction();
        try {
            $stmt = $conns->prepare("
                INSERT INTO history_order_receipts (id, username, room_id, room_name, status, created_at, summary, total, completed_at)
                SELECT id, username, room_id, room_name, status, created_at, summary, total, NOW()
                FROM order_receipts
                WHERE id = ?
            ");
            if (!$stmt) throw new Exception("Insert failed: " . $conns->error);

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conns->prepare("DELETE FROM order_receipts WHERE id = ?");
            if (!$stmt) throw new Exception("Delete failed: " . $conns->error);

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $conns->commit();
            $success = "Order #$id marked as done and archived.";
        } catch (Exception $e) {
            $conns->rollback();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}


// Fetch current orders
$orders = [];
$result = $conns->query("SELECT * FROM order_receipts ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Order Transactions</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f4; }
        .main-wrapper { display: flex; height: 100vh; }
        .content { flex: 1; padding: 40px; overflow-y: auto; background: white; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h2 { display: flex; align-items: center; font-size: 22px; gap: 8px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        th, td { padding: 12px 16px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #343a40; color: #fff; }
        tr:hover { background: #f1f1f1; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-right: 4px; }
        .btn-approve { background: #28a745; color: #fff; }
        .btn-reject { background: #dc3545; color: #fff; }
        .btn-done { background: #6c757d; color: #fff; }
        .btn-cancel { background: #ffc107; color: #000; }
        .success { color: green; margin-bottom: 10px; }
        .error { color: red; margin-bottom: 10px; }
        form { display: inline-block; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include 'Sidebar.php'; ?>
    <div class="content">
        <div class="header">
            <h2><span class="material-icons">restaurant</span> Order Transactions</h2>
            <h2><a href="transaction.php">Back to Bookings</a></h2>
        </div>

        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Summary</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['room_name']) ?> (<?= $order['room_id'] ?>)</td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td><?= nl2br(htmlspecialchars($order['summary'])) ?></td>
                    <td>₱<?= number_format($order['total'], 2) ?></td>
                <td>
    <?php
    $status = trim($order['status']);
    if ($status === 'Pending') :
    ?>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button class="btn btn-approve">Approve</button>
        </form>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="action" value="reject">
            <button class="btn btn-reject">Reject</button>
        </form>

    <?php elseif ($status === 'Done') : ?>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="action" value="done">
            <button class="btn btn-done">Done</button>
        </form>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="action" value="cancel">
            <button class="btn btn-cancel">Cancel</button>
        </form>

    <?php elseif ($status === '' || is_null($status)) : ?>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="action" value="cancel">
            <button class="btn btn-cancel">Delete</button>
        </form>
    <?php endif; ?>
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
