<?php
session_start();

$site_title = "Rage Room & Resto";
$tagline = "Smash. Eat. Unwind.";
$rooms_intro = "Let out your stress in our fully equipped rage rooms with bats, bottles, and blast-proof fun.";
$resto_intro = "Refuel with hearty meals and cold drinks after a rage session.";

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "resto_rant_management_system";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) && empty($password)) {
        $_SESSION['login_error_modal'] = "Both username and password are required.";
    } elseif (empty($username)) {
        $_SESSION['login_error_modal'] = "Username is required.";
    } elseif (empty($password)) {
        $_SESSION['login_error_modal'] = "Password is required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = strtolower($user['role']);
                $_SESSION['redirect_url'] = ($_SESSION['role'] === 'admin') ? 'LandingPage.php' : 'LandingPage.php';

        
                exit;
            } else {
                $_SESSION['login_error_modal'] = "Incorrect password.";
            }
        } else {
            $_SESSION['login_error_modal'] = "Username not found.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $site_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <li class="nav-item"><a class="nav-link" href="#rage">RageRoom</a></li>
                   <li class="nav-item"><a class="nav-link" href="#Restaurant">Resto</a></li>
          
          
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

<!-- Hero Section -->
<section class="hero-section text-white text-center d-flex align-items-center" style="background-image: url('../img/l.jpg'); background-size: cover; background-position: center; height: 50vh;">
    <div class="container">
        <h1 style="font-size: 70px; font-weight: bold;"><?= $site_title ?></h1>
        <p class="lead" style="font-size: 40px;"><?= $tagline ?></p>
    </div>
</section>

<section class="py-5" id="rage">
    <div class="container">
        <h2 class="text-center mb-4">Rage Room Experience</h2>
        <p class="text-center"><?= $rooms_intro ?></p>
        <div class="row mt-4" >
            <?php
            $rage_rooms = [
                ["img" => "../img/rageroom3.avif", "title" => "Smash Zone", "desc" => "Break plates, TVs, and glassware in our safest, most satisfying rage zone."],
                ["img" => "../img/rageroom1.jpg", "title" => "Office Mayhem", "desc" => "Tear apart printers, phones, and cubicle setups to relieve workplace stress."],
                ["img" => "../img/rageroom2.jpg", "title" => "Battle Room", "desc" => "Gear up with full protection and smash with bats, pipes, or sledgehammers."]
            ];
            foreach ($rage_rooms as $room): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $room['img'] ?>" class="card-img-top" alt="<?= $room['title'] ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $room['title'] ?></h5>
                            <p class="card-text"><?= $room['desc'] ?></p>
                            <button class="btn btn-primary book-btn" href="view_rooms.php">Book</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Restaurant Section -->
<section class="bg-light py-5"  id="Restaurant">
    <div class="container">
        <h2 class="text-center mb-4">Our Restaurant</h2>
        <p class="text-center"><?= $resto_intro ?></p>
        <div class="row mt-4">
            <?php
            $dishes = [
                ["img" => "../img/1.jpg", "title" => "Smash Burger", "desc" => "Juicy beef patty with spicy fries and secret sauce."],
                ["img" => "../img/2.jpg", "title" => "Wreck-It Wings", "desc" => "Crispy, spicy chicken wings served with cool ranch dip."],
                ["img" => "../img/3.jpg", "title" => "Craft Beers & Mocktails", "desc" => "Refresh with our hand-crafted drinks after your rage session."]
            ];
            foreach ($dishes as $dish): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $dish['img'] ?>" class="card-img-top" alt="<?= $dish['title'] ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= $dish['title'] ?></h5>
                            <p class="card-text"><?= $dish['desc'] ?></p>
           <button class="btn btn-primary order-btn">Order</button>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    <p>&copy; <?= date("Y") ?> <?= $site_title ?>. All rights reserved.</p>
</footer>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="LandingPage.php">
        <div class="modal-header">
          <h5 class="modal-title">Login Required</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if (isset($_SESSION['login_error_modal'])): ?>
              <div class="alert alert-danger"><?= $_SESSION['login_error_modal']; unset($_SESSION['login_error_modal']); ?></div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="text-center">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Book button logic -->
<script>
    // Handle Rage Room "Book" buttons
    document.querySelectorAll('.book-btn').forEach(button => {
        button.addEventListener('click', () => {
            <?php if (isset($_SESSION['username'])): ?>
                window.location.href = "<?= $_SESSION['role'] === 'admin' ? 'Home.php' : 'view_rooms.php' ?>";
            <?php else: ?>
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            <?php endif; ?>
        });
    });

    // Handle Resto "Order" buttons
    document.querySelectorAll('.order-btn').forEach(button => {
        button.addEventListener('click', () => {
            <?php if (isset($_SESSION['username'])): ?>
                window.location.href = "order_foods.php";
            <?php else: ?>
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            <?php endif; ?>
        });
    });
</script>


</body>
</html>
