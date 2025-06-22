<?php
session_start();
include "db.php";
conn();

if (!$conns) {
    die("<h2>Database connection error: " . mysqli_connect_error() . "</h2>");
}

// Handle Search & Filtering
$search = isset($_GET['search']) ? mysqli_real_escape_string($conns, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conns, $_GET['category']) : '';

// Build SQL Query with Search and Category Filters
$dataQuery = "SELECT id, title, content, category, img, user_id FROM posts WHERE 1=1";

if (!empty($search)) {
    $dataQuery .= " AND title LIKE '%$search%'";
}
if (!empty($category)) {
    $dataQuery .= " AND category = '$category'";
}

$dataQuery .= " ORDER BY id DESC";

// Execute Query
$contents = mysqli_query($conns, $dataQuery);

// Check for SQL errors
if (!$contents) {
    die("<h2>SQL Query Failed: " . mysqli_error($conns) . "</h2>");
}

// Handle Save Post Logic
if (isset($_POST['save'])) {
    // Safely capture inputs
    $title = mysqli_real_escape_string($conns, $_POST['title']);
    $content = mysqli_real_escape_string($conns, $_POST['content']);
    $category = mysqli_real_escape_string($conns, $_POST['category']);
    $post_id = mysqli_real_escape_string($conns, $_POST['post_id']);
    $user_id = $_SESSION['user_id'];
    $imagePath = mysqli_real_escape_string($conns, $_POST['image']);

    try {
        // Check if the user has already saved this post (check both post_id and user_id)
        $checkQuery = "SELECT * FROM savedpost WHERE id = '$post_id' AND userid = '$user_id'";
        $checkResult = mysqli_query($conns, $checkQuery);

        // Check if the query execution was successful
        if ($checkResult === false) {
            // Query failed, output an error message
            die("<h2>Error in checking saved posts: " . mysqli_error($conns) . "</h2>");
        }

        // Check if the post has already been saved by the user
        if (mysqli_num_rows($checkResult) > 0) {
            // Post already saved by this user, alert the user
            echo "<script>alert('You have already saved this post.');</script>";
        } else {
            // Prepare the SQL query to insert a new saved post
            $data = "INSERT INTO savedpost (title, content, category, userid, img, id) 
                     VALUES ('$title', '$content', '$category', '$user_id', '$imagePath', '$post_id')";
            $insertResult = mysqli_query($conns, $data);

            // Check for success
            if ($insertResult) {
                echo "<script>alert('Post saved successfully');location.href='main.php';</script>";
            } else {
                echo "<script>alert('Error occurred while saving the post');location.href='main.php';</script>";
            }
        }
    } catch (Exception $e) {
        // Handle any unexpected exceptions
        echo "<script>alert('Error: {$e->getMessage()}');location.href='main.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITS-KSU Announcements</title>
    <link rel="stylesheet" href="css/user_dashboard.css">
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="header-container">
            <img src="img/logo.png" alt="Logo" class="logo">
            <img src="img/its.png" alt="Logo" class="logo">
            <h3>ITS - Kalinga State University</h3>
        </div>
        <nav class="nav-links">
            <a href="my_profile.php">My Profile</a>
            <a href="logout.php"><img class="logouticon" src="img/logout.png" alt="Logout"></a>
        </nav>
    </header>

    <!-- Search and Filter Section -->
    <section class="search-section">
        <form method="GET" action="main.php">
            <input type="text" name="search" placeholder="Search announcements..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Events" <?php echo $category == 'Events' ? 'selected' : ''; ?>>Events</option>
                <option value="Announcements" <?php echo $category == 'Announcements' ? 'selected' : ''; ?>>Announcements</option>
                <option value="News" <?php echo $category == 'News' ? 'selected' : ''; ?>>News</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </section>

    <div class="content-section">
        <?php
        if ($contents) {
            while ($row = mysqli_fetch_assoc($contents)) {
                echo "<div class='post'>";
                echo "<form method='POST' action='main.php' class='post-form' enctype='multipart/form-data'>";
                echo "<button type='submit'class='save' name='save' class='delete-btn'>save</button>";
                echo "<input type='hidden' name='post_id' value='{$row['id']}'>";
                echo "<input type='text' name='title' class='post-title' value='" . htmlspecialchars($row['title']) . "' required>";
                if (!empty($row['img'])) {
                    echo "<img src='" . htmlspecialchars($row['img']) . "' alt='Post Image' class='post-image' />";
                }
                echo "<input type='text' name='image' value='{$row['img']}' hidden/>";
                echo "<textarea name='content' class='post-content' required>" . htmlspecialchars($row['content']) . "</textarea>";
                echo "<select name='category' class='post-category' hidden>";
                $categories = ['Events', 'Announcements', 'News']; // Add more categories if needed
                foreach ($categories as $cat) {
                    $selected = ($cat === $row['category']) ? "selected" : "";
                    echo "<option value='$cat' $selected>$cat</option>";
                }
                echo "</select>";
        
                echo "<div class='post-actions'>";
         
                echo "</div>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<h3>No announcements found. Try searching or filtering.</h3>";
        }
        ?>
    </div>
</body>
</html>



<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    header {
    background-color: #239255; 
    color: white;
    padding: 20px;
    text-align: center;
}.save{
    width: 80px;
    height: 30px;
    border-radius:  5px ;
    border: none;
   background-color:  #239255;
   color:white;
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.logo {
    width: 50px; 
    height: auto; 
    display: inline-block;
}


    nav.nav-links a {
        margin: 0 10px;
        color: white;
        text-decoration: none;
        font-weight: bold;
        font-size: 15px;
    }

    .search-section {
        margin: 20px;
        text-align: center;
    }

    .search-section input,
    .search-section select,
    .search-section button {
        padding: 8px;
        font-size: 14px;
        margin: 5px;
    }

    /* Post List Section */
    .content-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 20px;
    }

    .post {
        background: #ffffff;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: 80%;
        transition: transform 0.2s;
    }

    .post:hover {
        transform: translateY(-3px);
    }

    .post h2 {
        margin-bottom: 8px;
        font-size: 20px;
    }

    .post p {
        margin-bottom: 8px;
        font-size: 16px;
    }

    /* Post Action Buttons */
    .post-actions {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
    }

    .update-btn,
    .delete-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        color: white;
    }

    .update-btn {
        background-color: #007bff;
    }

    .delete-btn {
        background-color: #d9534f;
    }

    .update-btn:hover {
        background-color: #0056b3;
    }

    .delete-btn:hover {
        background-color: #c9302c;
    }
    .homeicon{
        width:45px;
        height: 45px;
    }
    .dashboardicon{
        width: 45px;
        height: 45px ;
    }
    .createicon{
        width: 50px ;
        height: 50px;
    }
    .logouticon{
        width: 45px;
        height: 45px;
    }
    .post {
    background: #ffffff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 80%;
    margin-bottom: 20px;
    transition: transform 0.2s;
}

.post:hover {
    transform: translateY(-3px);
}

.post-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.post-title,
.post-category,
.post-content {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.post-title {
    font-weight: bold;
    font-size: 20px;
}

.post-category {
    font-size: 16px;
    background-color: #f9f9f9;
}

.post-content {
    min-height: 100px;
    font-size: 16px;
}

.post-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.update-btn,
.delete-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: white;
    font-size: 14px;
}

.update-btn {
    background-color: #007bff;
}

.delete-btn {
    background-color: #d9534f;
}

.update-btn:hover {
    background-color: #0056b3;
}

.delete-btn:hover {
    background-color: #c9302c;
}.post-image {
    width: 100%; /* Make the image responsive */
    max-width: 300px; /* Limit maximum width */
    height: auto;
    margin-bottom: 15px;
    border-radius: 5px;
}.post-content {
min-height:500px;
    /* Limits the height to 500px */
        /* Allows vertical resizing by the user */
}
</style>
