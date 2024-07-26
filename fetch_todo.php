<?php
session_start(); // Start the session to access session variables

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$dbname = "proftal"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the UserID from the session
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($userID) {
    // Prepare and execute the select statement
    $sql = "SELECT Title, Due, Status FROM todo WHERE UserID = '$userID' AND Status =1";
    $result = $conn->query($sql);

    $tasks = [];
    if ($result->num_rows > 0) {
        // Fetch tasks and store in an array
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }

    // Output tasks as JSON
    echo json_encode($tasks);
} else {
    echo json_encode(["error" => "User not logged in"]);
}

// Close the connection
$conn->close();
?>
