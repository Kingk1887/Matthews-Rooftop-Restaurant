<?php
require_once 'db.php';

$message = '';
$upload_dir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $picture = '';

    // Handle file upload if present
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = basename($_FILES['image_file']['name']);
        $target_path = $upload_dir . uniqid() . '_' . $file_name;

        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $target_path)) {
            $picture = $target_path;
        } else {
            $message = '<div class="error-message">Failed to upload image.</div>';
        }
    } elseif ($image_url) {
        $picture = $image_url;
    }

    if ($name && $price !== '') {
        $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, picture) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $picture);
        if ($stmt->execute()) {
            $message = '<div class="success-message">Menu item added successfully!</div>';
        } else {
            $message = '<div class="error-message">Error: ' . htmlspecialchars($conn->error) . '</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="error-message">Name and price are required.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Menu Item - Matthews Rooftop Restaurant</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: white;
            color: black;
        }
        header { background-color: #003366; color: white; padding: 20px; text-align: center; }
        nav { background-color: #FFD700; display: flex; justify-content: center; padding: 10px 0; }
        nav a { color: black; text-decoration: none; margin: 0 20px; font-weight: bold; font-size: 1.1em; }
        nav a:hover { text-decoration: underline; }
        section { padding: 30px; text-align: center; }
        .highlight { color: #003366; }
        footer { background-color: black; color: white; text-align: center; padding: 15px; }
        input, textarea, button { padding: 10px; margin: 5px 0; width: 80%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc; font-size: 1em; }
        button { background-color: #003366; color: white; cursor: pointer; }
        button:hover { background-color: #002244; }
        form { display: flex; flex-direction: column; align-items: center; }
        .success-message { color: green; margin-bottom: 15px; }
        .error-message { color: red; margin-bottom: 15px; }
        .or-separator { margin: 10px 0; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h1>Add Menu Item</h1>
    </header>

    <nav class="admin-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="add_menu_item.php">Add Menu Item</a>
        <a href="update_menu.php">Update Menu</a>
    </nav>

    <section>
        <h2>Add a New Menu Item</h2>
        <?php if ($message) echo $message; ?>
        <form method="post" action="add_menu_item.php" enctype="multipart/form-data" autocomplete="off">
            <input type="text" name="name" placeholder="Dish Name" required>
            <textarea name="description" placeholder="Description (optional)" rows="3"></textarea>
            <input type="number" name="price" placeholder="Price (e.g. 19.99)" min="0" step="0.01" required>
            <div class="or-separator">Image: (choose one)</div>
            <input type="url" name="image_url" placeholder="Image URL (e.g. https://...)">
            <span style="margin: 5px 0;">OR</span>
            <input type="file" name="image_file" accept="image/*">
            <button type="submit">Add Item</button>
        </form>
    </section>

    <footer>
        &copy; 2025 Matthews Rooftop Restaurant. All rights reserved.
    </footer>
</body>
</html> 