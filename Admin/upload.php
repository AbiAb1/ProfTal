<?php
session_start();
include 'connection.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskTitle = $_POST['title'];
    $instructions = $_POST['instructions'];
    $department = $_POST['department'];
    $grade = $_POST['grade'];
    $due_date = $_POST['due-date'];
    $timestamp = date('Y-m-d H:i:s');

    if (mysqli_query($conn, $sql_insert_content)) {
        $contentID = mysqli_insert_id($conn);

        // Insert into tasks table
        $sql_insert_task = "INSERT INTO tasks (ContentID, Type, Title, DueDate, taskContent, TimeStamp) 
                            VALUES ('$grade', 'Task', '$taskTitle', '$due_date', '$instructions', '$timestamp')";

        if (mysqli_query($conn, $sql_insert_task)) {
            $taskID = mysqli_insert_id($conn);

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['file']['name'];
                $file_tmp = $_FILES['file']['tmp_name'];
                $file_size = $_FILES['file']['size'];
                $file_type = $_FILES['file']['type'];
                $upload_dir = 'uploads/';

                if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                    $uri = $upload_dir . $file_name;

                    // Insert into documents table
                    $sql_insert_file = "INSERT INTO documents (UserID, ContentID, TaskID, name, mimeType, size, uri, Status, timestamp) 
                                        VALUES ('{$_SESSION['user_id']}', '$grade', '$taskID', '$file_name', '$file_type', '$file_size', '$uri', 1, '$timestamp')";

                    if (mysqli_query($conn, $sql_insert_file)) {
                        $response['success'] = true;
                        $response['message'] = 'Task and file created successfully.';
                    } else {
                        $response['message'] = 'Error: ' . mysqli_error($conn);
                    }
                } else {
                    $response['message'] = 'Error moving uploaded file.';
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Task created successfully.';
            }
        } else {
            $response['message'] = 'Error: ' . mysqli_error($conn);
        }
    } else {
        $response['message'] = 'Error: ' . mysqli_error($conn);
    }
}

echo json_encode($response);
?>
