<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include your database connection file here
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

// Continue with file upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
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
}

// Close database connection
mysqli_close($conn);
?>
