<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resto_rant_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";

// Define variables for retaining values
$name = $email = $phone = $address = $username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $fields_valid = true;

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($username)) {
        $error_message = "All fields are required.";
        $fields_valid = false;
    }

    if (empty($password) || empty($confirm_password)) {
        $error_message = "Password and Confirm Password are required.";
        $fields_valid = false;
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
        $fields_valid = false;
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error_message = "Password must include at least one uppercase letter and one number.";
        $fields_valid = false;
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
        $fields_valid = false;
    }

    if ($fields_valid) {
        $check_sql = "SELECT id FROM users WHERE username = '$username'";
        $check_result = $conn->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            $error_message = "Username already exists. Please choose another.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (name, email, phone, address, username, password)
                           VALUES ('$name', '$email', '$phone', '$address', '$username', '$hashed_password')";

            if ($conn->query($insert_sql) === TRUE) {
                $success_message = "Account created successfully. You can now log in.";
                $name = $email = $phone = $address = $username = ""; // Clear values on success
            } else {
                $error_message = "Error creating account: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
                     background-image: url('./img/l.jpg');
        }

        .signup-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .signup-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .login-prompt {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        .login-prompt a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif (!empty($success_message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="input-group">
                <label for="address">Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p class="login-prompt">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</body>
</html>
