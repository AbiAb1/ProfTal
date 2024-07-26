<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("You need to log in to post a comment.");
}

require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $incoming_id = $_SESSION['user_id'];
    $outgoing_id = $_POST['outgoing_id'];
    $task_id = $_POST['task_id'];
    $content_id = $_POST['content_id'];
    $message = $_POST['message'];

    // Validate input
    if (empty($incoming_id) || empty($outgoing_id) || empty($task_id) || empty($content_id) || empty($message)) {
        die("All fields are required.");
    }

    // Insert comment into database
    $sql = "INSERT INTO comments (ContentID, TaskID, Incoming_id, Outgoing_id, Comment) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiiss", $content_id, $task_id, $incoming_id, $outgoing_id, $message);
        if ($stmt->execute()) {
            echo "Comment posted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
