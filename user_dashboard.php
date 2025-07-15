<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/user_dashboard.css">
</head>
<body>
    <header>
        <h1>User Dashboard</h1>
        <nav class="nav-links">
            <a href="my_profile.php">My Profile</a>
            <a href="logout.php">Log Out</a>
        </nav>
    </header>

    <div class="welcome-section">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Here's what's available for you to explore:</p>
    </div>

    <div class="features">
        <div class="feature-card">
            <h3>My Profile</h3>
            <p>View and update your personal information.</p>
            <a href="my_profile.php">Go to Profile</a>
        </div>
            
    </div>
</body>
</html>
