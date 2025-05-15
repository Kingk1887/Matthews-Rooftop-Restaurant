<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the data from the form (assuming using POST method)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = $_POST['dish_name'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Insert data into the database
    $sql = "INSERT INTO reviews (dish_name, rating, review_text) VALUES ('$dish_name', '$rating', '$review_text')";

    if ($conn->query($sql) === TRUE) {
        echo "New review added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>