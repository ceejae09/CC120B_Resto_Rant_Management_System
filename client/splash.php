<?php
session_start();

if (!isset($_SESSION['redirect_url'])) {
    header("Location:LandingPage.php");
    exit();
}

$redirect_url = $_SESSION['redirect_url'];
unset($_SESSION['redirect_url']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($redirect_url); ?>">
    <style>
        :root {
            --primary: #58641D;
            --bg: #fdfdfd;
            --text: #333;
            --muted: #777;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--bg);
            height: 100vh;
            overflow: hidden;
        }

        .splash-container {
            text-align: center;
            animation: fadeOut 1s ease-in-out 2.5s forwards;
        }

        .spinner {
            margin: 0 auto 20px;
            width: 50px;
            height: 50px;
            border: 6px solid rgba(0, 0, 0, 0.1);
            border-top: 6px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        h2 {
            font-size: 1.8rem;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        p {
            font-size: 1rem;
            color: var(--muted);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: scale(0.95); }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <div class="spinner" role="status" aria-label="Loading..."></div>
        <h2>Loading...</h2>
        <p>Things getting ready.</p>
    </div>
</body>
</html>
