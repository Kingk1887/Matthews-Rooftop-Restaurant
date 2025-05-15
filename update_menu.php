<?php
require_once 'db.php';

$message = '';

// Handle price update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_price"])) {
    $itemId = (int)$_POST["item_id"];
    $newPrice = (float)$_POST["new_price"];
    $query = "UPDATE menu_items SET price = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $newPrice, $itemId);

    if ($stmt->execute()) {
        $message = "Price updated successfully!";
    } else {
        $message = "Error updating price!";
    }
    $stmt->close();
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_item"])) {
    $itemId = (int)$_POST["item_id"];
    $query = "DELETE FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        $message = "Menu item deleted successfully!";
    } else {
        $message = "Error deleting menu item!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Menu – Matthews Rooftop</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        h2 { color: #003366; }
        form { background: white; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        label { display: block; margin: 10px 0 5px; }
        input, select, button { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { background: #003366; color: white; border: none; cursor: pointer; }
        .delete-btn { background: #c0392b; }
        .message { margin-top: 20px; font-size: 16px; color: green; }
        .admin-nav {
            background-color: #FFD700;
            display: flex;
            justify-content: center;
            padding: 15px 0;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
        }
        .admin-nav a {
            color: #003366;
            text-decoration: none;
            margin: 0 25px;
            font-weight: bold;
            font-size: 1.1em;
            transition: color 0.2s;
        }
        .admin-nav a:hover {
            color: #222;
            text-decoration: underline;
        }
        .menu-list { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; margin-bottom: 30px; }
        .menu-item-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .menu-item-name { font-weight: bold; }
        .menu-item-actions { display: flex; gap: 10px; }
    </style>
</head>
<body>
<nav class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="add_menu_item.php">Add Menu Item</a>
    <a href="update_menu.php">Update Menu</a>
</nav>

<h2>Update Menu Item Price</h2>
<form method="POST">
    <label for="item_id">Select Item</label>
    <select name="item_id" id="item_id" required>
        <?php
        $items = $conn->query("SELECT id, name FROM menu_items ORDER BY name ASC");
        while ($row = $items->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>

    <label for="new_price">New Price (e.g., 49.99)</label>
    <input type="number" name="new_price" step="0.01" min="0" required>
    <button type="submit" name="update_price">Update Price</button>
</form>

<h2>Delete Menu Item</h2>
<div class="menu-list">
    <?php
    $items = $conn->query("SELECT id, name, price FROM menu_items ORDER BY name ASC");
    while ($row = $items->fetch_assoc()) {
        echo "<div class='menu-item-row'>";
        echo "<span class='menu-item-name'>{$row['name']} (&#36;{$row['price']})</span>";
        echo "<form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this item?');\">";
        echo "<input type='hidden' name='item_id' value='{$row['id']}'>";
        echo "<button type='submit' name='delete_item' class='delete-btn'>Delete</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
</div>

<?php if ($message) { echo "<p class='message'>$message</p>"; } ?>

</body>
</html> 