<?php
session_start();
include 'connection.php';

// Check if the uploaded_files table exists, create it if necessary
$sql_check_table = "SHOW TABLES LIKE 'uploaded_files'";
$result_check_table = mysqli_query($conn, $sql_check_table);

if (mysqli_num_rows($result_check_table) == 0) {
    // Table doesn't exist, create it
    $sql_create_table = "
        CREATE TABLE uploaded_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            UserID INT NOT NULL,
            TaskID INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            mimeType VARCHAR(255) NOT NULL,
            size INT NOT NULL,
            uri VARCHAR(255) NOT NULL,
            submitted BOOLEAN DEFAULT 0, 
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";

    if (mysqli_query($conn, $sql_create_table)) {
        echo "Table 'uploaded_files' created successfully.<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Handle file upload and submission status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileUpload'])) {
        // File upload process
        $file_name = $_FILES['fileUpload']['name'];
        $file_tmp = $_FILES['fileUpload']['tmp_name'];
        $file_size = $_FILES['fileUpload']['size'];
        $file_error = $_FILES['fileUpload']['error'];
        $file_type = $_FILES['fileUpload']['type'];

        // Validate file upload errors
        if ($file_error !== UPLOAD_ERR_OK) {
            echo '<p class="text-danger">Error uploading file.</p>';
            exit();
        }

        // Get task_id from the form (assuming it's passed via POST)
        $task_id = $_POST['task_id']; // Adjust as needed

        // Define upload directory
        $upload_dir = 'uploads/';

        // Move uploaded file to designated directory
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            // Prepare data for database insertion
            $uri = $upload_dir . $file_name; // URI to store in database
            $timestamp = date('Y-m-d H:i:s'); // Current timestamp

            // Insert file details into database table
            $sql_insert_file = "INSERT INTO uploaded_files (UserID, TaskID, name, mimeType, size, uri, submitted, timestamp) 
                                VALUES ('{$_SESSION['user_id']}', '$task_id', '$file_name', '$file_type', '$file_size', '$uri', 1, '$timestamp')";
            
            $result = mysqli_query($conn, $sql_insert_file);

            if ($result) {
                echo '<p class="text-success">File uploaded and marked as submitted.</p>';
            } else {
                echo '<p class="text-danger">Error: ' . mysqli_error($conn) . '</p>';
            }
        } else {
            echo '<p class="text-danger">Error moving uploaded file.</p>';
        }
    } elseif (isset($_POST['unsubmit'])) {
        // Unsubmit process
        $task_id = $_POST['task_id'];
        $user_id = $_SESSION['user_id'];

        // Update the submitted status to 0 instead of deleting
        $sql_update_status = "UPDATE uploaded_files SET submitted = 0 WHERE UserID = '$user_id' AND TaskID = '$task_id'";
        
        if (mysqli_query($conn, $sql_update_status)) {
            echo '<p class="text-success">File unsubmitted successfully.</p>';
        } else {
            echo '<p class="text-danger">Error updating status: ' . mysqli_error($conn) . '</p>';
        }
    }
}

// Close database connection
mysqli_close($conn);
?>

<!-- HTML Form for File Upload and Management -->
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="fileUpload" required>
    <input type="hidden" name="task_id" value="1"> <!-- Replace with your dynamic task_id -->
    <button type="submit">Submit File</button>
</form>

<form action="" method="post">
    <input type="hidden" name="task_id" value="1"> <!-- Replace with your dynamic task_id -->
    <button type="submit" name="unsubmit">Unsubmit File</button>
</form>
