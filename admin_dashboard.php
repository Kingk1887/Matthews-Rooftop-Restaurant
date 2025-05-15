<?php
require_once 'db.php';

// Handle reservation open/close toggle using a file
$lock_file = 'reservations_closed.txt';
if (isset($_POST['toggle_reservations'])) {
    if (file_exists($lock_file)) {
        unlink($lock_file); // Open reservations
    } else {
        file_put_contents($lock_file, 'closed'); // Close reservations
    }
}
$reservations_closed = file_exists($lock_file);

// --- Auto-delete reservations 3 minutes past their reservation time ---
$now = new DateTime();
$delete_sql = "DELETE FROM reservations WHERE CONCAT(reservation_date, ' ', reservation_time) < ? AND TIMESTAMPDIFF(MINUTE, CONCAT(reservation_date, ' ', reservation_time), ?) > 3";
$stmt = $conn->prepare($delete_sql);
$now_str = $now->format('Y-m-d H:i:s');
$stmt->bind_param("ss", $now_str, $now_str);
$stmt->execute();
$stmt->close();

// --- Handle date selection and search ---
$selected_date = $_GET['date'] ?? date('Y-m-d');
$search_name = trim($_GET['search_name'] ?? '');
$filter_email = trim($_GET['filter_email'] ?? '');

// Fetch reservations for the selected date (default: today)
$sql = "SELECT * FROM reservations WHERE reservation_date = ? ORDER BY reservation_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$reservations = $stmt->get_result();

// --- For filter dropdown: get all emails for the selected day ---
$email_result = $conn->prepare("SELECT DISTINCT email FROM reservations WHERE reservation_date = ?");
$email_result->bind_param("s", $selected_date);
$email_result->execute();
$email_list = $email_result->get_result()->fetch_all(MYSQLI_ASSOC);
$email_result->close();

// --- For navigation: get week days ---
$week = [];
$today = new DateTime();
for ($i = -3; $i <= 3; $i++) {
    $d = clone $today;
    $d->modify("$i day");
    $week[] = $d;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – Reservations</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        h2 { color: #003366; }
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
        .dashboard-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            max-width: 900px;
            margin: 0 auto;
        }
        .week-nav { margin-bottom: 20px; text-align: center; }
        .week-nav a {
            margin: 0 5px;
            padding: 6px 12px;
            border-radius: 5px;
            background: #eee;
            color: #003366;
            text-decoration: none;
            font-weight: bold;
        }
        .week-nav a.selected, .week-nav a:hover {
            background: #003366;
            color: #fff;
        }
        .search-bar, .filter-bar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-bar input, .filter-bar select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th { background: #003366; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        .no-res { text-align: center; color: #888; margin: 30px 0; }
    </style>
</head>
<body>
<nav class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="add_menu_item.php">Add Menu Item</a>
    <a href="update_menu.php">Update Menu</a>
</nav>

<!-- Reservation toggle button for admin -->
<form method="post" style="text-align:center; margin-bottom:20px;">
    <button type="submit" name="toggle_reservations"
        style="padding:10px 30px; background:<?php echo $reservations_closed ? '#27ae60' : '#c0392b'; ?>; color:white; border:none; border-radius:5px; font-size:1.1em;">
        <?php echo $reservations_closed ? 'Open Reservations to Public' : 'Close Reservations to Public'; ?>
    </button>
</form>
<?php if ($reservations_closed): ?>
    <div style="color:red; text-align:center; font-weight:bold;">
        Reservations are currently CLOSED to the public.
    </div>
<?php endif; ?>

<div class="dashboard-section">
    <h2>Reservations for <?php echo htmlspecialchars($selected_date); ?></h2>
    <form method="get" style="margin-bottom:20px;">
        <label for="date">Select date:</label>
        <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date); ?>">
        <button type="submit">View</button>
    </form>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr style="background:#003366; color:#fff;">
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Special Request</th>
            <th>Created At</th>
        </tr>
        <?php if ($reservations->num_rows > 0): ?>
            <?php while ($row = $reservations->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars(date('g:i A', strtotime($row['reservation_time']))); ?></td>
                <td><?php echo htmlspecialchars($row['number_of_guests']); ?></td>
                <td><?php echo htmlspecialchars($row['special_request']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center; color:#888;">No reservations for this date.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>