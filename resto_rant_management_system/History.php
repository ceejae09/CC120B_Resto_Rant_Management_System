<?php
include 'db.php';
conn();
global $conns;

$history = [];
$result = $conns->query("SELECT * FROM history_transactions ORDER BY completed_at DESC");
while ($r = $result->fetch_assoc()) {
    $history[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Transaction History</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
  body { margin:0; display:flex; height:100vh; background:#f4f4f4; font-family:Arial,sans-serif; }
  .main-wrapper { display:flex; width:100%; }
  .content { flex:1; padding:40px; overflow-y:auto; background:white; }
  .header { display:flex; align-items:center; margin-bottom:20px; }
  .header h2 { display:flex; align-items:center; gap:8px; font-size:22px; }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.05); }
  th,td { padding:12px 16px; text-align:left; border-bottom:1px solid #e0e0e0; }
  th { background:#343a40; color:#fff; }
  tr:hover { background:#f1f1f1; }
</style>
</head>
<body>

<div class="main-wrapper">
  <?php include 'Sidebar.php'; ?>
  <div class="content">
    <div class="header">
      <h2><span class="material-icons">history</span> Transaction History</h2>
    </div>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Room</th>
          <th>Price</th>
          <th>Date</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Completed At</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($history) === 0): ?>
          <tr><td colspan="8" style="text-align:center;">No history found.</td></tr>
        <?php else: ?>
          <?php foreach ($history as $tx): ?>
            <tr>
              <td><?= $tx['transaction_id'] ?></td>
              <td><?= htmlspecialchars($tx['username']) ?></td>
              <td><?= htmlspecialchars($tx['room_name']) ?></td>
              <td>₱<?= number_format($tx['price'], 2) ?></td>
              <td><?= htmlspecialchars($tx['date_to_avail']) ?></td>
              <td><?= htmlspecialchars($tx['status']) ?></td>
              <td><?= htmlspecialchars($tx['created_at']) ?></td>
              <td><?= htmlspecialchars($tx['completed_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</div>

</body>
</html>
