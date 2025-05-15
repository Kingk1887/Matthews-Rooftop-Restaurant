<?php
include 'db.php'; // Use the shared connection

// Reservation lock file logic
$lock_file = 'reservations_closed.txt';
if (isset($_POST['toggle_reservations'])) {
    if (file_exists($lock_file)) {
        unlink($lock_file); // Open reservations
    } else {
        file_put_contents($lock_file, 'closed'); // Close reservations
    }
}
$reservations_closed = file_exists($lock_file);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $date    = $_POST['date'] ?? '';
    $time    = $_POST['time'] ?? '';
    $guests  = intval($_POST['guests'] ?? 1);
    $special = trim($_POST['special-requests'] ?? '');

    // Basic validation
    if ($name && $email && $phone && $date && $time && $guests > 0) {
        // Check if the date is today or in the future
        $today = date('Y-m-d');
        if ($date < $today) {
            $message = '<div style="color:red;">You cannot book a reservation for a past date.</div>';
        } else {
            // Check if reservation is at least 2 hours in advance
            $reservationDateTime = strtotime("$date $time");
            $now = time();
            //if ($reservationDateTime - $now < 2 * 3600) {
              //  $message = '<div style="color:red;">Reservations must be made at least 2 hours in advance.</div>';
            //} else {
                // Prepare and execute the insert using mysqli
                $stmt = $conn->prepare("INSERT INTO reservations (full_name, email, phone, reservation_date, reservation_time, number_of_guests, special_request) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssssis", $name, $email, $phone, $date, $time, $guests, $special);
                    if ($stmt->execute()) {
                        $message = '<div style="color:green;">Reservation submitted successfully!</div>';
                    } else {
                        $message = '<div style="color:red;">Database error: ' . htmlspecialchars($stmt->error) . '</div>';
                    }
                    $stmt->close();
                } else {
                    $message = '<div style="color:red;">Database error: ' . htmlspecialchars($conn->error) . '</div>';
                }
            //}
        }
    } else {
        $message = '<div style="color:red;">Please fill in all required fields.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Matthews Rooftop Restaurant</title>
    <style>
        /* ... existing styles ... */
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
        input, select, textarea, button { padding: 10px; margin: 5px 0; width: 80%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc; font-size: 1em; }
        button { background-color: #003366; color: white; cursor: pointer; }
        button:hover { background-color: #002244; }
        form { display: flex; flex-direction: column; align-items: center; }
        img { max-width: 100%; height: auto; display: block; margin: 15px auto; }
    </style>
</head>
<body>
    <header>
        <img src="https://static.wixstatic.com/media/6eb5e0_ab8bcfe7842140529e1ce8a989dc292c~mv2_d_2429_2429_s_4_2.jpeg/v1/crop/x_0,y_187,w_2429,h_2055/fill/w_532,h_450,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/6eb5e0_ab8bcfe7842140529e1ce8a989dc292c~mv2_d_2429_2429_s_4_2.jpeg" alt="Matthews Rooftop Restaurant">
        <h1>Matthews Rooftop Restaurant</h1>
    </header>

    <nav>
        <a href="index.html">Home</a>
        <a href="about.html">About Us</a>
        <a href="menu.php">Menu</a>
        <a href="reservations.php">Reservation</a>
        <a href="contact.html">Contact</a>
    </nav>
    
    <?php if ($reservations_closed): ?>
        <div style="color:red; font-weight:bold; margin:30px 0;">
            Reservations are currently closed by the administrator. Please check back later.
        </div>
        <form>
            <!-- All your input fields, but the button is disabled -->
            <input type="text" name="name" placeholder="Full Name" required disabled>
            <input type="email" name="email" placeholder="Email Address" required disabled>
            <input type="tel" name="phone" placeholder="Phone Number" required disabled>
            <input type="date" name="date" required disabled>
            <input type="time" name="time" required disabled>
            <input type="number" name="guests" placeholder="Number of Guests" min="1" required disabled>
            <textarea name="special-requests" placeholder="Special Requests (Optional)" rows="3" disabled></textarea>
            <button type="submit" disabled style="background: #ccc; color: #888; cursor: not-allowed;">Book Now</button>
        </form>
    <?php else: ?>
        <section id="reservations" style="background-color: #e6f2ff;">
            <h2>Make a Reservation</h2>
            <p>Reserve your table with us today!</p>
            <?php if ($message) echo $message; ?>
            <form method="post" action="reservations.php" autocomplete="off">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="tel" name="phone" placeholder="Phone Number" required>
                <input type="date" name="date" required>
                <input type="time" name="time" required>
                <input type="number" name="guests" placeholder="Number of Guests" min="1" required>
                <textarea name="special-requests" placeholder="Special Requests (Optional)" rows="3"></textarea>
                <button type="submit">Book Now</button>
            </form>
        </section>
    <?php endif; ?>

    <footer>
        &copy; 2025 Matthews Rooftop Restaurant. All rights reserved.
        <br>
        <a href="admin.php" style="color: gray; font-size: 0.9em;">Admin Login</a>
    </footer>
</body>
</html> 