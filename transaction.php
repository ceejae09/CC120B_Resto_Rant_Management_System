<?php
include 'db.php';
conn();
global $conns;

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['txn_id'], $_POST['action'])) {
    $id = intval($_POST['txn_id']);
    $action = $_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $stmt = $conns->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
        $stmt->bind_param("si", $new_status, $id);
        $stmt->execute();
        $stmt->close();
        $success = "Transaction #$id marked as $new_status.";

    } elseif ($action === 'done') {
        // Get the room_id before deleting the transaction
        $stmt = $conns->prepare("SELECT room_id FROM transactions WHERE transaction_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($room_id);
        $stmt->fetch();
        $stmt->close();

        // Move transaction to history
        $stmt = $conns->prepare("
            INSERT INTO history_transactions (transaction_id, username, room_id, room_name, price, date_to_avail, created_at, status, completed_at)
            SELECT transaction_id, username, room_id, room_name, price, date_to_avail, created_at, status, NOW()
            FROM transactions
            WHERE transaction_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Delete the transaction
        $stmt = $conns->prepare("DELETE FROM transactions WHERE transaction_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Update the room status to "Available"
        if (!empty($room_id)) {
            $stmt = $conns->prepare("UPDATE rage_rooms SET status = 'Available' WHERE id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $stmt->close();
        }

        $success = "Transaction #$id marked as done and room set to available.";

    } elseif ($action === 'delete') {
        $stmt = $conns->prepare("DELETE FROM transactions WHERE transaction_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $success = "Transaction #$id deleted.";
    }
}

// Fetch all current transactions
$txns = [];
$result = $conns->query("SELECT * FROM transactions ORDER BY created_at DESC");
while ($r = $result->fetch_assoc()) {
    $txns[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Transactions</title>
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
      <h2><span class="material-icons">receipt_long</span> Booking Transactions</h2>
      <h2 class="text-secondary ms-3"><a href="Order_transaction.php">
        View Order 

      </a></h2>
    </div>

    <?php if($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Phone</th>
          <th>Room</th>
          <th>Price</th>
          <th>Date</th>
          <th>Status</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($txns as $tx): ?>
        <tr>
          <td><?= $tx['transaction_id'] ?></td>
          <td><?= htmlspecialchars($tx['username']) ?></td>
          <td><?= htmlspecialchars($tx['phone_number']) ?></td>
          <td><?= htmlspecialchars($tx['room_name']) ?></td>
          <td>₱<?= number_format($tx['price'], 2) ?></td>
          <td><?= htmlspecialchars($tx['date_to_avail']) ?></td>
          <td><?= htmlspecialchars($tx['status']) ?></td>
          <td><?= htmlspecialchars($tx['created_at']) ?></td>
          <td>
            <?php if ($tx['status'] === 'Pending'): ?>
              <form method="POST">
                <input type="hidden" name="txn_id" value="<?= $tx['transaction_id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-approve">Approve</button>
              </form>
              <form method="POST">
                <input type="hidden" name="txn_id" value="<?= $tx['transaction_id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-reject">Reject</button>
              </form>
            <?php elseif ($tx['status'] === 'Approved'): ?>
              <form method="POST">
                <input type="hidden" name="txn_id" value="<?= $tx['transaction_id'] ?>">
                <input type="hidden" name="action" value="done">
                <button class="btn btn-done">Done</button>
              </form>
            <?php elseif ($tx['status'] === 'Rejected'): ?>
              <form method="POST">
                <input type="hidden" name="txn_id" value="<?= $tx['transaction_id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-delete">Delete</button>
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
