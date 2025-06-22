<!-- sidebar.php -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
  .sidebar {
    background-color: #1976d2;
    color: white;
    transition: width 0.3s;
    overflow: hidden;
    height: 100vh;
    width: 60px;
    position: fixed;
  }

  .sidebar.open {
    width: 200px;
  }

  .sidebar .toggle-btn {
    padding: 10px;
    cursor: pointer;
    background-color: #1565c0;
    text-align: center;
  }

  .menu {
    display: flex;
    flex-direction: column;
    margin-top: 20px;
  }

  .menu a {
    text-decoration: none;
    color: white;
  }

  .menu-item {
    padding: 15px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
  }

  .menu-item:hover {
    background-color: #1565c0;
  }

  .menu-item i {
    margin-right: 10px;
  }

  .menu-label {
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .sidebar.open .menu-label {
    opacity: 1;
  }

  .content {
    margin-left: 60px;
    padding: 20px;
    transition: margin-left 0.3s;
  }

  .sidebar.open ~ .content {
    margin-left: 200px;
  }
</style>

<div class="sidebar" id="sidebar">
  <div class="toggle-btn" onclick="toggleSidebar()">
    <i class="material-icons">menu</i>
  </div>
  <div class="menu">
    <a href="transaction.php" class="menu-item">
      <i class="material-icons">payment</i>
      <span class="menu-label">Transaction</span>
    </a>
    <a href="resto.php" class="menu-item">
      <i class="material-icons">restaurant</i>
      <span class="menu-label">Resto</span>
    </a>
    <a href="Room.php" class="menu-item">
      <i class="material-icons">meeting_room</i>
      <span class="menu-label">Room</span>
    </a>
  <a href="History.php" class="menu-item">
  <i class="material-icons">history</i>
  <span class="menu-label">History</span>
</a>

  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  function toggleSidebar() {
    sidebar.classList.toggle('open');
    document.querySelector('.content')?.classList.toggle('sidebar-open');
  }
</script>
