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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_testimonial']) && isset($_POST['testimony'], $_SESSION['username'])) {
    $message = trim($_POST['testimony']);
    $username = $_SESSION['username'];


    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO testimonials (username, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $message);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

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
                  echo "<script>window.location.href='splash.php';</script>";

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
if (isset($_POST['submit'])) {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $testimony = mysqli_real_escape_string($conn, $_POST['testimony']);
    $rating = intval($_POST['rating']); // Convert to integer
    $username = $_SESSION['username'];

    $query = "INSERT INTO testimonials(username, message, rating) 
              VALUES ('$username', '$testimony', $rating)";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>alert('Testimony submitted successfully!');</script>";
    } else {
        $error = mysqli_error($conn);
        echo "<script>alert('Error submitting testimony: " . addslashes($error) . "');</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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
    <li class="nav-item">
        <a class="nav-link" href="transaction_request.php">Transaction</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="LandingPage.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-danger" href="logout.php">Logout</a> <!-- Logout button -->
    </li>
<?php else: ?>
    <li class="nav-item">
        <a class="nav-link text-success" href="/resto_rant_management_system/client/loginPage.php">Login</a> <!-- Login button -->
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
<!-- What is a Rage Room Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                <i class="bi bi-explosion-fill text-danger me-2"></i>What is a Rage Room?
            </h2>
            <p class="lead mt-3">
                A Rage Room is a safe space where you can break everyday objects like glassware, electronics, or furniture—fully geared up and stress-free. No judgment, just smash!
            </p>
        </div>

        <h4 class="text-center mb-4">
            <i class="bi bi-gear-wide-connected text-primary me-2"></i>How It Works
        </h4>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-md-4">
                <div class="p-4 bg-white shadow rounded h-100 text-center">
                    <i class="bi bi-calendar-check-fill fs-1 text-success mb-3"></i>
                    <h5 class="fw-bold">1. Book Your Room</h5>
                    <p>Pick your rage room online or on-site and schedule your session easily.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="col-md-4">
                <div class="p-4 bg-white shadow rounded h-100 text-center">
                    <i class="bi bi-shield-check fs-1 text-info mb-3"></i>
                    <h5 class="fw-bold">2. Suit Up</h5>
                    <p>We’ll get you geared up with helmets, gloves, and body armor for total safety.</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="col-md-4">
                <div class="p-4 bg-white shadow rounded h-100 text-center">
                    <i class="bi bi-hammer fs-1 text-danger mb-3"></i>
                    <h5 class="fw-bold">3. Choose Your Weapon</h5>
                    <p>Grab your favorite smashing tool—bat, crowbar, or sledgehammer—it's game time.</p>
                </div>
            </div>
            <!-- Step 4 -->
            <div class="col-md-6">
                <div class="p-4 bg-white shadow rounded h-100 text-center">
                    <i class="bi bi-building-down fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">4. Smash Things</h5>
                    <p>Destroy TVs, plates, furniture, and more in a safe, controlled rage room environment.</p>
                </div>
            </div>
            <!-- Step 5 -->
            <div class="col-md-6">
                <div class="p-4 bg-white shadow rounded h-100 text-center">
                    <i class="bi bi-cup-straw fs-1 text-purple mb-3"></i>
                    <h5 class="fw-bold">5. Relax & Refuel</h5>
                    <p>Head to our resto after your rage session and enjoy burgers, wings, and cool drinks.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
// Fetch testimonials
$testimonials = [];
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if (!$conn->connect_error) {
    $result = $conn->query("SELECT username, message, rating, created_at FROM testimonials ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}
?>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold">Reviews:</h2>
            <?php if (isset($_SESSION['username'])): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testimonialModal">Write Your Feedback</button>
            <?php endif; ?>
        </div>

        <?php if (!empty($testimonials)): ?>
            <div class="position-relative">
                <!-- Left Arrow -->
                <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y z-1" id="prevBtn" style="z-index: 10;">
                    <i class="bi bi-chevron-left fs-3 text-black"></i>
                </button>

                <!-- Testimonial Cards Wrapper -->
                <div class="d-flex overflow-hidden" style="scroll-behavior: smooth;" id="testimonialWrapper">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card flex-shrink-0 px-2">
                            <div class="card p-4 shadow-sm text-center h-100">
                                <blockquote class="blockquote mb-0">
                                    <p>"<?= htmlspecialchars($testimonial['message']) ?>"</p>
                                    <footer class="blockquote-footer mt-2">- <?= htmlspecialchars($testimonial['username']) ?></footer>
                                </blockquote>
                                <!-- Star Rating -->
                                <div class="mt-2 text-warning">
                                    <?php
                                        $stars = intval($testimonial['rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $stars
                                                ? '<i class="bi bi-star-fill"></i>'
                                                : '<i class="bi bi-star"></i>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Right Arrow -->
                <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y z-1" id="nextBtn" style="z-index: 10;">
                    <i class="bi bi-chevron-right fs-3 text-black"></i>
                </button>
            </div>

            <style>
                #testimonialWrapper {
                    display: flex;
                    gap: 1rem;
                }

                .testimonial-card {
                    width: 33.33%;
                    transition: transform 0.3s ease-in-out;
                }

                .testimonial-card:nth-child(3n+2) {
                    transform: scale(1.1); /* center card larger */
                }

                @media (max-width: 768px) {
                    .testimonial-card {
                        width: 100%;
                        transform: none !important;
                    }
                }
            </style>

            <script>
                const wrapper = document.getElementById('testimonialWrapper');
                const cardWidth = wrapper.querySelector('.testimonial-card').offsetWidth + 16;
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                prevBtn.addEventListener('click', () => {
                    wrapper.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                });

                nextBtn.addEventListener('click', () => {
                    wrapper.scrollBy({ left: cardWidth, behavior: 'smooth' });
                });
            </script>
        <?php else: ?>
            <p class="text-muted text-center">No customer reviews yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Bootstrap Icons (include in your <head> or before closing </body> tag) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    <p>&copy; <?= date("Y") ?> <?= $site_title ?>. All rights reserved.</p>
</footer>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" >
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

<!-- Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="LandingPage.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Write Your Reviews</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="testimony" class="form-label">Your message</label>
                    <textarea name="testimony" id="testimony" class="form-control" required rows="4"></textarea>
                </div>
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select name="rating" id="rating" class="form-select" required>
                        <option value="">Select a rating</option>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
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
                window.location.href = "<?= $_SESSION['role'] === 'admin' ? 'view_rooms.php' : 'view_rooms.php' ?>";
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
