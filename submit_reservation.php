<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Capture form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$time = $_POST['time'];
$guests = $_POST['guests'];
$special_requests = $_POST['special-requests'];

// Prepare SQL query to insert data into reservations table
$sql = "INSERT INTO reservations (name, email, phone, date, time, guests, special_requests)
        VALUES ('$name', '$email', '$phone', '$date', '$time', '$guests', '$special_requests')";

if ($conn->query($sql) === TRUE) {
  echo "New reservation made successfully.";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>