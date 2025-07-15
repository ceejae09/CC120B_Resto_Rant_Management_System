<?php
include 'db.php';
conn();
global $conns;

$success = $error = "";

// Handle Delete Action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $stmt = $conns->prepare("DELETE FROM history_order_receipts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success = "Order #$deleteId deleted successfully.";
            } else {
                $error = "Delete failed: Order not found.";
            }
        } else {
            $error = "Delete failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "SQL Error: " . $conns->error;
    }
}

// Fetch all order history
$orders = [];
$result = $conns->query("SELECT * FROM history_order_receipts ORDER BY completed_at DESC");
while ($r = $result->fetch_assoc()) {
    $orders[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Order History</title>
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
      <h2><span class="material-icons">receipt_long</span> Order History</h2>
              
            <h2><a href="History.php">Back to Booking History</a></h2>
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
          <th>Total</th>
          <th>Created</th>
          <th>Completed</th>
          <th>Summary</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($orders) === 0): ?>
          <tr><td colspan="9" style="text-align:center;">No order history found.</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars($order['username']) ?></td>
              <td><?= htmlspecialchars($order['room_name']) ?> (<?= $order['room_id'] ?>)</td>
              <td><?= htmlspecialchars($order['status']) ?></td>
              <td>₱<?= number_format($order['total'], 2) ?></td>
              <td><?= htmlspecialchars($order['created_at']) ?></td>
              <td><?= htmlspecialchars($order['completed_at']) ?></td>
              <td class="summary-box"><?= nl2br(htmlspecialchars($order['summary'])) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete order #<?= $order['id'] ?>?');">
                  <input type="hidden" name="delete_id" value="<?= $order['id'] ?>">
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</div>

<?php if ($success): ?>
  <script>alert("<?= addslashes($success) ?>");</script>
<?php elseif ($error): ?>
  <script>alert("<?= addslashes($error) ?>");</script>
<?php endif; ?>

</body>
</html>
