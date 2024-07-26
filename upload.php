<?php
session_start();

// Redirect to index.php if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'connection.php';

$response = array('success' => false);

// Check if task_id and content_id are set
if (isset($_POST['task_id']) && isset($_POST['content_id'])) {
    $task_id = $_POST['task_id'];
    $content_id = $_POST['content_id'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Check if files were uploaded
    if (!empty($_FILES['files']['name'][0])) {
        $uploadDirectory = 'Documents/'; // Set your upload directory

        foreach ($_FILES['files']['name'] as $key => $name) {
            $fileTmpName = $_FILES['files']['tmp_name'][$key];
            $originalFileName = $name;
            $fileName = time() . '_' . $name; // Create a unique file name with timestamp
            $fileDestination = $uploadDirectory . $fileName;
            $fileSize = $_FILES['files']['size'][$key];
            $fileType = $_FILES['files']['type'][$key]; // MIME type of the file

            // Move uploaded file to destination
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Check if file with same name exists in database
                $checkQuery = "SELECT ID FROM documents WHERE UserID = '$user_id' AND TaskID = '$task_id' AND ContentID = '$content_id' AND name = '$originalFileName'";
                $checkResult = mysqli_query($conn, $checkQuery);

                if (mysqli_num_rows($checkResult) > 0) {
                    // File already exists, update its status to 1
                    $row = mysqli_fetch_assoc($checkResult);
                    $fileID = $row['ID'];
                    $updateQuery = "UPDATE documents SET Status = 1 WHERE ID = '$fileID'";
                    if (mysqli_query($conn, $updateQuery)) {
                        $response['success'] = true;
                        // Log the ID of the updated file
                        error_log("Updated file ID: $fileID");
                    } else {
                        $response['error'] = "Error updating existing file status: " . mysqli_error($conn);
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    // File does not exist, insert new file info into database
                    $sql = "INSERT INTO documents (UserID, TaskID, ContentID, name, uri, mimeType, size, Status) 
                            VALUES ('$user_id', '$task_id', '$content_id', '$originalFileName', '$fileDestination', '$fileType', '$fileSize', 1)";
                    if (mysqli_query($conn, $sql)) {
                        $response['success'] = true;
                    } else {
                        $response['error'] = "Error inserting new file info into database: " . mysqli_error($conn);
                        echo json_encode($response);
                        exit();
                    }
                }
            } else {
                $response['error'] = "Failed to upload file: $name";
                echo json_encode($response);
                exit();
            }
        }
        
        // Update status to 1 for existing files that were resubmitted
        if (!empty($_POST['existing_file_ids'])) {
            $existingFileIds = $_POST['existing_file_ids'];
            foreach ($existingFileIds as $fileId) {
                $updateQuery = "UPDATE documents SET Status = 1 WHERE ID = '$fileId'";
                if (!mysqli_query($conn, $updateQuery)) {
                    $response['error'] = "Error updating existing file status: " . mysqli_error($conn);
                    echo json_encode($response);
                    exit();
                }
            }
        }
    } else {
        $response['error'] = 'No files uploaded.';
    }
} else {
    $response['error'] = 'Task ID or Content ID missing.';
}

mysqli_close($conn);

// Return JSON response
echo json_encode($response);
?>
