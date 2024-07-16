<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];

    // Update the submitted status to 0 instead of deleting
    $sql_update_status = "UPDATE uploaded_files SET submitted = 0 WHERE UserID = '$user_id' AND TaskID = '$task_id'";
    
    if (mysqli_query($conn, $sql_update_status)) {
        // Optionally, you can also delete the file from the server if needed
        // Example: unlink($file_uri);
        
        header("Location: taskdetails.php?task_id=$task_id");
        exit();
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
}

// Close database connection
mysqli_close($conn);
?>
