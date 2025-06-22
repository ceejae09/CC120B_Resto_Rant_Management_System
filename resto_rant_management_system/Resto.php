<?php
include 'db.php';
conn();
global $conns;

$success = $error = "";

// Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_menu_id'])) {
    $id = intval($_POST['delete_menu_id']);
    $stmt = $conns->prepare("DELETE FROM resto_menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Menu deleted successfully.";
    } else {
        $error = "Failed to delete menu.";
    }
    $stmt->close();
}

// Add
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name']) && !isset($_POST['edit_menu_id'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $photo_path = "";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = $target_file;
        } else {
            $error = "Failed to upload image.";
        }
    }

    if (!$error) {
        $stmt = $conns->prepare("INSERT INTO resto_menu (name, description, price, category, photo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $photo_path);
        if ($stmt->execute()) {
            $success = "Menu added successfully.";
        } else {
            $error = "Error saving menu.";
        }
        $stmt->close();
    }
}

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_menu_id'])) {
    $id = intval($_POST['edit_menu_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $stmt = $conns->prepare("UPDATE resto_menu SET name=?, description=?, price=?, category=? WHERE id=?");
    $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
    if ($stmt->execute()) {
        $success = "Menu updated successfully.";
    } else {
        $error = "Failed to update menu.";
    }
    $stmt->close();
}

// Fetch menu
$menu = [];
$result = $conns->query("SELECT * FROM resto_menu ORDER BY category, name");
while ($row = $result->fetch_assoc()) {
    $menu[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resto Menu</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { margin: 0; font-family: Arial; background: #f5f5f5; }
        .main-wrapper { display: flex; height: 100vh; }
        .content { flex: 1; padding: 30px; overflow-y: auto; background: white; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-delete { background: #d32f2f; color: white; }
        .btn-edit { background: #1976d2; color: white; }
        .btn-add { background: #388e3c; color: white; }
        .dialog-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; background: rgba(0,0,0,0.3); justify-content: center; align-items: center; z-index: 1000; }
        .dialog { background: white; padding: 25px; border-radius: 6px; max-width: 400px; width: 100%; }
        .dialog form input, .dialog form textarea, .dialog form select { width: 100%; margin-bottom: 10px; padding: 8px; }
        .dialog h3 { margin-top: 0; }
        .close-btn { float: right; cursor: pointer; font-size: 18px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
        img.menu-img { max-width: 80px; height: auto; }
        #filterInput { padding: 8px; margin-bottom: 15px; width: 100%; max-width: 400px; }
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="header">
            <h2><span class="material-icons">restaurant</span> Resto Menu</h2>
            <button class="btn btn-add" onclick="openAddDialog()">+ Add Item</button>
        </div>

        <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

        <input type="text" id="filterInput" placeholder="Search by name or category...">

        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody id="menuTableBody">
            <?php foreach ($menu as $item): ?>
                <tr data-name="<?= strtolower($item['name']) ?>" data-category="<?= strtolower($item['category']) ?>">
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td>
                        <?php if (!empty($item['photo'])): ?>
                            <img src="<?= htmlspecialchars($item['photo']) ?>" class="menu-img">
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="delete_menu_id" value="<?= $item['id'] ?>">
                            <button class="btn btn-delete" onclick="return confirm('Delete this item?')">Delete</button>
                        </form>
                        <button class="btn btn-edit" onclick='openEditDialog(<?= json_encode($item) ?>)'>Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Dialog -->
<div class="dialog-overlay" id="addDialog">
    <div class="dialog">
        <div class="close-btn" onclick="closeAddDialog()">&times;</div>
        <h3>Add Menu Item</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" name="price" step="0.01" placeholder="Price" required>
            <select name="category" required>
                <option value="Meal">Meal</option>
                <option value="Drink">Drink</option>
                <option value="Snack">Snack</option>
            </select>
            <input type="file" name="photo" accept="image/*">
            <button type="submit">Add</button>
        </form>
    </div>
</div>

<!-- Edit Dialog -->
<div class="dialog-overlay" id="editDialog">
    <div class="dialog">
        <div class="close-btn" onclick="closeEditDialog()">&times;</div>
        <h3>Edit Menu Item</h3>
        <form method="POST">
            <input type="hidden" name="edit_menu_id" id="edit_id">
            <input type="text" name="name" id="edit_name" required>
            <textarea name="description" id="edit_description" required></textarea>
            <input type="number" name="price" step="0.01" id="edit_price" required>
            <select name="category" id="edit_category" required>
                <option value="Meal">Meal</option>
                <option value="Drink">Drink</option>
                <option value="Snack">Snack</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </div>
</div>

<script>
function openAddDialog() {
    document.getElementById("addDialog").style.display = "flex";
}
function closeAddDialog() {
    document.getElementById("addDialog").style.display = "none";
}
function openEditDialog(data) {
    document.getElementById("edit_id").value = data.id;
    document.getElementById("edit_name").value = data.name;
    document.getElementById("edit_description").value = data.description;
    document.getElementById("edit_price").value = data.price;
    document.getElementById("edit_category").value = data.category;
    document.getElementById("editDialog").style.display = "flex";
}
function closeEditDialog() {
    document.getElementById("editDialog").style.display = "none";
}

// Filter
document.getElementById('filterInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#menuTableBody tr');
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const category = row.getAttribute('data-category');
        if (name.includes(filter) || category.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
