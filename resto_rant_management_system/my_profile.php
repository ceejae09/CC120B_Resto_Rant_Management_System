<?php 
    session_start();
    include "db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f7fc;
        }

        header {
            background-color: #239255;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        nav img {
            height: 50px;
            width: auto;
        }

        nav h3 {
            font-size: 1.1rem;
            font-weight: 400;
            color: white;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #f4f7fc;
            text-decoration: underline;
        }

        .container {
            display: flex;
            flex: 1;
            padding: 20px;
            justify-content: flex-start;
            gap: 30px;
            padding-top: 40px;
        }

        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
            font-size: 1.1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #575757;
        }

        .content {
            flex: 1;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .content h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .content-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .post {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .post:hover {
            transform: translateY(-5px);
        }

        .post-title,
        .post-content {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .post-title:focus,
        .post-content:focus {
            border-color: #239255;
            outline: none;
        }

        .post-actions {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
        }

        .update-btn,
        .delete-btn {
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        .update-btn {
            background-color: #007bff;
            color: white;
        }

        .delete-btn {
            background-color: #d9534f;
            color: white;
        }

        .update-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn:hover {
            background-color: #c9302c;
        }

        .post-image {
            width: 100%;
            max-width: 300px;
            height: auto;
            margin: 20px 0;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }

            .content {
                width: 100%;
            }

            .post {
                width: 100%;
            }

            header h1 {
                font-size: 1.5rem;
            }
        }.post-content {
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s, height 0.3s ease;
    min-height: 150px;  /* Minimum height to keep consistent size */
    resize: vertical; /* Allows vertical resizing */
    overflow-y: auto; 
    height: 500px; /* Enables scrolling when content is long */
}

.post-content:focus {
    border-color: #239255;
    outline: none;
   /* Expanding the height when focused */
}

    </style>
</head>
<body>
    <?php
        // Establish database connection
        conn();

        if (!$conns) {
            die("<h2>Database connection error: " . mysqli_connect_error() . "</h2>");
        }

        // Ensure the user is logged in and `user_id` exists
        if (!isset($_SESSION['user_id'])) {
            die("<h2>Error: User not logged in. Please log in to view saved posts.</h2>");
        }
        $user_id = mysqli_real_escape_string($conns, $_SESSION['user_id']);

        // Handle Post Deletion
        if (isset($_POST['delete_post_id'])) {
            $post_id = mysqli_real_escape_string($conns, $_POST['delete_post_id']);
            $deleteQuery = "DELETE FROM savedpost WHERE userid = '$user_id' AND id = '$post_id'";

            if (mysqli_query($conns, $deleteQuery)) {
                echo "<script>alert('Post deleted successfully');</script>";
            } else {
                echo "<script>alert('Error deleting post');</script>";
            }
        }

        // Handle Search & Filtering
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conns, $_GET['search']) : '';
        $category = isset($_GET['category']) ? mysqli_real_escape_string($conns, $_GET['category']) : '';

        // Build SQL Query with Search and Category Filters
        $dataQuery = "SELECT title, content, category, img, userid, id FROM savedpost WHERE userid = '$user_id'";

        if (!empty($search)) {
            $dataQuery .= " AND title LIKE '%$search%'";
        }
        if (!empty($category)) {
            $dataQuery .= " AND category = '$category'";
        }

        // Replace `id` with `userid` or remove ORDER BY if not needed
        $dataQuery .= " ORDER BY userid DESC"; 

        $contents = mysqli_query($conns, $dataQuery);

        if (!$contents) {
            die("<h2>Error fetching posts: " . mysqli_error($conns) . "</h2>");
        }
    ?>

    <header>
        <h1>My Profile</h1>
        <nav>
            <img src="img/logo.png" alt="Logo" class="logo">
            <img src="img/its.png" alt="Logo" class="logo">
            <h3>ITS - Kalinga State University</h3>
        </nav>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="main.php">Home</a></li>
                <li><a href="login.php">Logout</a></li>
            </ul>
        </aside>

        <main class="content">
            <h2>Saved Posts</h2>
            <div class="content-section">
                <?php
                if ($contents) {
                    while ($row = mysqli_fetch_assoc($contents)) {
                        echo "<div class='post'>";
                        echo "<form method='POST' action='my_profile.php' class='post-form' enctype='multipart/form-data'>";
                        echo "<input type='hidden' name='post_id' value='{$row['id']}'>";

                        // Title as editable input
                        echo "<h2 name='title' class='post-title' value='" . htmlspecialchars($row['title']) . "' required>{$row['title']}</h2>";

                        // Display current image if available
                        if (!empty($row['img'])) {
                            echo "<img src='" . htmlspecialchars($row['img']) . "' alt='Post Image' class='post-image' />";
                        }

                        // Content as editable textarea
                        echo "<textarea name='content' class='post-content' required>" . htmlspecialchars($row['content']) . "</textarea>";

                        // Buttons for Update and Delete
                        echo "<div class='post-actions'>";
                        echo "<button type='submit' name='delete_post_id' value='{$row['id']}' class='delete-btn'>Delete</button>";
                        echo "</div>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<h3>No posts found. Try searching or filtering.</h3>";
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
