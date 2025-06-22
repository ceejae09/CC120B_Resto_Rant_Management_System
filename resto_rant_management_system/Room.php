<?php
include 'db.php';
conn(); // $conns is now available
global $conns;

$success = $error = "";

// Handle room deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_room_id'])) {
    $room_id = intval($_POST['delete_room_id']);

    // Delete image file
    $stmt = $conns->prepare("SELECT image_path FROM rage_rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->bind_result($imgPath);
    if ($stmt->fetch() && file_exists($imgPath)) {
        unlink($imgPath);
    }
    $stmt->close();

    // Delete DB record
    $stmt = $conns->prepare("DELETE FROM rage_rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    if ($stmt->execute()) {
        $success = "Room deleted successfully.";
    } else {
        $error = "Failed to delete room.";
    }
    $stmt->close();
}

// Handle new room creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name']) && !isset($_POST['edit_room_id'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $price = floatval($_POST['price']);
    $room_type = trim($_POST['room_type']);
    $props = trim($_POST['props']);

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        $image_file_type = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

        $allowed = ["jpg", "jpeg", "png", "gif"];
        if (in_array($image_file_type, $allowed)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                $stmt = $conns->prepare("INSERT INTO rage_rooms (name, description, price, room_type, props, image_path) VALUES ( ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdsss", $name, $description, $price, $room_type, $props, $image_path);

                if ($stmt->execute()) {
                    $success = "Room added successfully.";
                } else {
                    $error = "Error saving room info.";
                }
                $stmt->close();
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type.";
        }
    } else {
        $error = "Please upload an image.";
    }
}

// Handle room update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room_id'])) {
    $room_id = intval($_POST['edit_room_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $room_type = trim($_POST['room_type']);
    $props = trim($_POST['props']);
    $price = floatval($_POST['price']);

    $new_image_path = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $new_image_path = $target_dir . basename($_FILES["image"]["name"]);
        $image_file_type = strtolower(pathinfo($new_image_path, PATHINFO_EXTENSION));

        $allowed = ["jpg", "jpeg", "png", "gif"];
        if (in_array($image_file_type, $allowed)) {
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $new_image_path)) {
                $error = "Failed to upload new image.";
                $new_image_path = null;
            }
        } else {
            $error = "Invalid image type.";
            $new_image_path = null;
        }
    }

    if (!$error) {
        if ($new_image_path) {
            $stmt = $conns->prepare("SELECT image_path FROM rage_rooms WHERE id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $stmt->bind_result($old_path);
            if ($stmt->fetch() && file_exists($old_path)) {
                unlink($old_path);
            }
            $stmt->close();

            $stmt = $conns->prepare("UPDATE rage_rooms SET name=?, description=?, room_type=?, props=?, price=?, image_path=? WHERE id=?");
            $stmt->bind_param("ssssdsi", $name, $description, $room_type, $props, $price, $new_image_path, $room_id);
        } else {
            $stmt = $conns->prepare("UPDATE rage_rooms SET name=?, description=?, room_type=?, props=?, price=? WHERE id=?");
            $stmt->bind_param("ssssdi", $name, $description, $room_type, $props, $price, $room_id);
        }

        if ($stmt->execute()) {
            $success = "Room updated successfully.";
        } else {
            $error = "Failed to update room.";
        }
        $stmt->close();
    }
}

// Fetch rooms
$rooms = [];
$result = $conns->query("SELECT * FROM rage_rooms ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rage Rooms</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { margin: 0; display: flex; height: 100vh; background: #f4f4f4; font-family: Arial, sans-serif; }
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .card img { width: 100%; height: 180px; object-fit: cover; border-radius: 6px; margin-bottom: 10px; }
        .card h3 { margin: 0; }
        .card form { margin-top: auto; }
        .card button.btn { width: 100%; padding: 10px; margin-top: 10px; background-color: #c62828; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
        .btn-edit { background-color: #1976d2; margin-top: 5px; }
        .dialog-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: none; justify-content: center; align-items: center; }
        .dialog { background: white; padding: 30px; border-radius: 8px; width: 100%; max-width: 500px; position: relative; }
        .dialog h2 { margin-top: 0; }
        .dialog form input, .dialog form textarea, .dialog form select, .dialog form button { width: 100%; margin-bottom: 12px; padding: 10px; }
        .dialog .close-btn { position: absolute; top: 10px; right: 15px; cursor: pointer; font-size: 20px; }
        .success { color: green; }
        .error { color: red; }
        .btn-open-dialog { padding: 10px 20px; background-color: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="header">
        <h2><span class="material-icons">meeting_room</span> Rage Rooms</h2>
        <button class="btn-open-dialog" onclick="openDialog()">+ Add Room</button>
    </div>

    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <div class="room-grid">
        <?php foreach ($rooms as $room): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="Room Image">
                <h3><?= htmlspecialchars($room['name']) ?></h3>
                <p><?= htmlspecialchars($room['description']) ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($room['room_type']) ?></p>
                <p><strong>Props:</strong> <?= htmlspecialchars($room['props']) ?></p>
       
                <p><strong>Price:</strong> $<?= $room['price'] ?></p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this room?');">
                    <input type="hidden" name="delete_room_id" value="<?= $room['id'] ?>">
                    <button type="submit" class="btn">🗑 Delete</button>
                </form>
                <button type="button" class="btn btn-edit" onclick='openEditDialog(<?= json_encode($room) ?>)'>✏ Edit</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Room Dialog -->
<div class="dialog-overlay" id="dialog">
    <div class="dialog">
        <div class="close-btn" onclick="closeDialog()">✖</div>
        <h2>Add Rage Room</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Room Name" required>
            <textarea name="description" placeholder="Description" rows="3"></textarea>
  
            <select name="room_type" required>
                <option value="">Select Room Type</option>
                <option value="Solo">Solo</option>
                <option value="Couple">Couple</option>
                <option value="Group">Group</option>
            </select>
            <input type="text" name="props" placeholder="Props (e.g., Bats, Plates)">
            <input type="number" name="price" placeholder="Price ($)" step="0.01" required>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Add Room</button>
        </form>
    </div>
</div>

<!-- Edit Room Dialog -->
<div class="dialog-overlay" id="editDialog">
    <div class="dialog">
        <div class="close-btn" onclick="closeEditDialog()">✖</div>
        <h2>Edit Rage Room</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_room_id" id="edit_room_id">
            <input type="text" name="name" id="edit_name" placeholder="Room Name" required>
            <textarea name="description" id="edit_description" placeholder="Description" rows="3"></textarea>
            <select name="room_type" id="edit_room_type" required>
                <option value="">Select Room Type</option>
                <option value="Solo">Solo</option>
                <option value="Couple">Couple</option>
                <option value="Group">Group</option>
            </select>
            <input type="text" name="props" id="edit_props" placeholder="Props (e.g., Bats, Plates)">
            <input type="number" name="price" id="edit_price" placeholder="Price ($)" step="0.01" required>
            <input type="file" name="image" accept="image/*">
            <button type="submit">Update Room</button>
        </form>
    </div>
</div>

<script>
    function openDialog() {
        document.getElementById("dialog").style.display = "flex";
    }

    function closeDialog() {
        document.getElementById("dialog").style.display = "none";
    }

    function openEditDialog(room) {
        document.getElementById("edit_room_id").value = room.id;
        document.getElementById("edit_name").value = room.name;
        document.getElementById("edit_description").value = room.description;
        document.getElementById("edit_room_type").value = room.room_type;
        document.getElementById("edit_props").value = room.props;
        document.getElementById("edit_price").value = room.price;
        document.getElementById("editDialog").style.display = "flex";
    }

    function closeEditDialog() {
        document.getElementById("editDialog").style.display = "none";
    }
</script>

</body>
</html>
