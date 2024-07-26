<?php
session_start();
require 'Connection.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You need to log in to view the conversation.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if (empty($content_id) || empty($task_id)) {
    echo json_encode(['error' => 'Content ID and Task ID are required.']);
    exit();
}

// Get the conversation messages ordered by ID
$sql = "SELECT comments.Comment, comments.Incoming_id, comments.Outgoing_id, useracc.fname, useracc.lname, useracc.Profile
        FROM comments 
        JOIN useracc ON comments.Incoming_id = useracc.UserID 
        WHERE comments.ContentID = ? AND comments.TaskID = ? AND (comments.Incoming_id = ? OR comments.Outgoing_id = ?) 
        ORDER BY comments.ID";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iiii", $content_id, $task_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        // Prepend the profile picture path
        $row['Profile'] = 'img/UserProfile/' . $row['Profile'];
        $row['FullName'] = $row['fname'] . ' ' . $row['lname'];
        $messages[] = $row;
    }
    echo json_encode(['messages' => $messages]);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error retrieving messages']);
}

$conn->close();
?>
