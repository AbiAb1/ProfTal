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

// Check if data was posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the UserID from the session
    $userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Get the task title and due date from the POST request
    $title = isset($_POST['taskName']) ? $conn->real_escape_string($_POST['taskName']) : '';
    $due = isset($_POST['taskDate']) ? $conn->real_escape_string($_POST['taskDate']) : '';

    // Validate input
    if ($userID && !empty($title) && !empty($due)) {
        // Prepare and execute the insert statement
        $sql = "INSERT INTO todo (UserID, Title, Due, Status) VALUES ('$userID', '$title', '$due', 1)";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Please provide all required fields."]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
