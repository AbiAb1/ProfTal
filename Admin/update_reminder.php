<?php
// Database connection
include 'connection.php';

// Handle AJAX request for updating a task
if (isset($_POST['update_task_id'])) {
    $taskID = $_POST['update_task_id'];
    $contentID = $_POST['edit_grade'];
    $title = $_POST['update_title'];
    $dueDate = $_POST['update_due_date'];
    $taskContent = $_POST['update_instructions'];

    // Log update details to logfile.log
    error_log("Update Task - TaskID: $taskID, ContentID: $contentID, Title: $title, DueDate: $dueDate, Instructions: $taskContent\n", 3, 'logfile.log');

    $sql = "UPDATE tasks SET ContentID = ?, Title = ?, DueDate = ?, taskContent = ? WHERE TaskID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $contentID, $title, $dueDate, $taskContent, $taskID);

    $response = array();
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Task updated successfully!';
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to update task.';
        // Log error to logfile.log
        error_log("Update Task Error: " . $stmt->error . "\n", 3, 'logfile.log');
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>