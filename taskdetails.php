<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include your database connection file here
include 'connection.php';

// Initialize variables
$task_title = "";
$task_description = "";
$task_type = "";
$task_due_date = "";
$submitted = false;

// Check if task_id is provided in URL
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Function to update submitted status from 0 to 1
    function updateSubmittedStatus($conn, $user_id, $task_id) {
        $sql_update_status = "UPDATE uploaded_files 
                              SET submitted = 1 
                              WHERE UserID = '$user_id' AND TaskID = '$task_id' AND submitted = 0";
        
        if (mysqli_query($conn, $sql_update_status)) {
            return true;
        } else {
            return false;
        }
    }

    // Query to fetch task details based on TaskID
    $sql_taskdetails = "SELECT * FROM tasks WHERE TaskID = '$task_id'";
    $result_taskdetails = mysqli_query($conn, $sql_taskdetails);

    // Check if task details are found
    if (mysqli_num_rows($result_taskdetails) > 0) {
        $row_taskdetails = mysqli_fetch_assoc($result_taskdetails);
        $task_title = $row_taskdetails['Title'];
        $task_description = $row_taskdetails['taskContent'];
        $task_type = $row_taskdetails['Type'];
        $task_due_date = $row_taskdetails['Duedate']; // Assuming DueDate is the column name
    } else {
        echo "Task details not found.";
        exit(); // Exit if task details are not found
    }
} else {
    echo "Task ID not provided.";
    exit(); // Exit if task ID is not provided
}

// Format Due Date
$formatted_due_date = date('F j, Y \a\t g:i A', strtotime($task_due_date));

// Initialize $submitted based on the current status in the database
$sql_check_submission = "SELECT submitted FROM uploaded_files WHERE UserID = '{$_SESSION['user_id']}' AND TaskID = '$task_id'";
$result_check_submission = mysqli_query($conn, $sql_check_submission);

if ($result_check_submission && mysqli_num_rows($result_check_submission) > 0) {
    $row_submission = mysqli_fetch_assoc($result_check_submission);
    $submitted = (bool) $row_submission['submitted']; // Convert to boolean
} else {
    $submitted = false; // Default to false if no submission found
}

// Handle file upload if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        // Update submission status to 1 or insert if not exists
        $task_id = $_GET['task_id'];
        $user_id = $_SESSION['user_id'];

        // Update status from 0 to 1 if necessary
        updateSubmittedStatus($conn, $user_id, $task_id);

        // File upload process
        $uploadErrors = [];
        $filesUploaded = false; // Flag to track if files were uploaded

        // Loop through each file
        foreach ($_FILES['fileUpload']['name'] as $key => $filename) {
            $file_name = $_FILES['fileUpload']['name'][$key];
            $file_tmp = $_FILES['fileUpload']['tmp_name'][$key];
            $file_size = $_FILES['fileUpload']['size'][$key];
            $file_error = $_FILES['fileUpload']['error'][$key];
            $file_type = $_FILES['fileUpload']['type'][$key];

            // Validate file upload errors
            if ($file_error === UPLOAD_ERR_OK) {
                // Ensure upload directory exists
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Move uploaded file to designated directory
                $new_file_name = uniqid() . '_' . $file_name; // Generate unique file name
                $uri = $upload_dir . $new_file_name; // URI to store in database

                if (move_uploaded_file($file_tmp, $uri)) {
                    // Prepare data for database insertion
                    $timestamp = date('Y-m-d H:i:s'); // Current timestamp

                    // Insert file details into database table
                    $sql_insert_file = "INSERT INTO uploaded_files (UserID, TaskID, name, mimeType, size, uri, submitted, timestamp) 
                                        VALUES ('{$_SESSION['user_id']}', '$task_id', '$file_name', '$file_type', '$file_size', '$uri', 1, '$timestamp')";
                    
                    $result = mysqli_query($conn, $sql_insert_file);

                    if (!$result) {
                        $uploadErrors[] = "Error inserting file '$file_name' into database.";
                    } else {
                        $filesUploaded = true; // Set flag indicating files were uploaded
                    }
                } else {
                    $uploadErrors[] = "Error moving uploaded file '$file_name'.";
                }
            } else {
                $uploadErrors[] = "Error uploading file '$file_name'.";
            }
        }

        // Display upload errors if any
        if (!empty($uploadErrors)) {
            foreach ($uploadErrors as $error) {
                echo '<p class="text-danger">' . $error . '</p>';
            }
        } else {
            $submitted = true;
            echo '<p class="text-success">All files uploaded and marked as submitted.</p>';
        }
    } elseif (isset($_POST['unsubmit'])) {
        // Unsubmit process
        $task_id = $_POST['task_id'];
        $user_id = $_SESSION['user_id'];

        // Update the submitted status to 0 instead of deleting
        $sql_update_status = "UPDATE uploaded_files SET submitted = 0 WHERE UserID = '$user_id' AND TaskID = '$task_id'";
        
        if (mysqli_query($conn, $sql_update_status)) {
            // Reset submitted status
            $submitted = false; // Ensure this sets $submitted to false
        } else {
            echo '<p class="text-danger">Error updating status: ' . mysqli_error($conn) . '</p>';
        }
    } elseif (isset($_POST['delete'])) {
        // Delete process
        $file_id = $_POST['file_id'];

        // Fetch file details to get URI for deletion from server
        $sql_fetch_file = "SELECT uri FROM uploaded_files WHERE id = '$file_id'";
        $result_fetch_file = mysqli_query($conn, $sql_fetch_file);

        if ($result_fetch_file && mysqli_num_rows($result_fetch_file) > 0) {
            $row = mysqli_fetch_assoc($result_fetch_file);
            $file_uri = $row['uri'];

            // Delete file from server
            if (unlink($file_uri)) {
                // Delete file record from database
                $sql_delete_file = "DELETE FROM uploaded_files WHERE id = '$file_id'";
                if (mysqli_query($conn, $sql_delete_file)) {
                    echo '<p class="text-success">File deleted successfully.</p>';
                } else {
                    echo '<p class="text-danger">Error deleting file: ' . mysqli_error($conn) . '</p>';
                }
            } else {
                echo '<p class="text-danger">Error deleting file from server.</p>';
            }
        } else {
            echo '<p class="text-danger">File not found or already deleted.</p>';
        }
    }
}

/// Initialize uploaded_files array
$uploaded_files = [];

// Fetch submitted files for the current task (submitted = 1 and files with non-zero size)
$sql_fetch_files = "SELECT * FROM uploaded_files WHERE UserID = '{$_SESSION['user_id']}' AND TaskID = '$task_id' AND submitted = 1 AND size > 0";
$result_fetch_files = mysqli_query($conn, $sql_fetch_files);

if ($result_fetch_files) {
    while ($row = mysqli_fetch_assoc($result_fetch_files)) {
        $uploaded_files[] = $row;
    }
}

// After unsubmitting, include files where submitted = 0 and size > 0
if (isset($_POST['unsubmit'])) {
    $sql_fetch_unsubmitted = "SELECT * FROM uploaded_files WHERE UserID = '{$_SESSION['user_id']}' AND TaskID = '$task_id' AND submitted = 0 AND size > 0";
    $result_fetch_unsubmitted = mysqli_query($conn, $sql_fetch_unsubmitted);
    
    if ($result_fetch_unsubmitted) {
        while ($row = mysqli_fetch_assoc($result_fetch_unsubmitted)) {
            $uploaded_files[] = $row;
        }
    }
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
    <!-- ======= Styles ====== -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .taskDetails {
            padding: 20px;
            border-radius: 8px;
            margin-left: 50px; /* Margin only on the left */
            margin-right: 50px; /* Margin only on the right */
        }
        .taskDetails h2 {
            font-size: 36px;
            margin-bottom: 10px;
            display: flex;
            align-items: center; /* Align items vertically */
            justify-content: space-between; /* Distribute items evenly */
        }
        .taskDetails p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
        }
        .taskDetails .taskType {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .icon-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px; /* Adjust circle size */
            height: 60px; /* Adjust circle size */
            background-color: #9B2035; /* Circle background color */
            border-radius: 50%; /* Make it a circle */
        }
        .icon1 {
            color: white; /* Icon color */
            font-size: 24px; /* Adjust icon size */
        }
        .taskDueDate {
            margin-top: -15px;
            margin-bottom: 20px; /* Adjusted from 50px to 20px */
            font-size: 14px; /* Adjusted font size */
            color: #999; /* Adjusted color */
        }
        .bin-icon {
            color: #f00; /* Red color for bin icon */
            cursor: pointer; /* Pointer cursor for hover effect */
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand"><i class='bx bxs-smile icon'></i> AdminSite</a>
        <?php include 'navbar.php'; ?>
    </section>
    <!-- SIDEBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include 'topbar.php'; ?>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div></div>
            <h1 style="padding-left:50px; margin-bottom: 20px;">Task Details</h1>
            <!-- ======================= Task Details ================== -->
            <div class="taskDetails">
                <h2>
                    <?php echo $task_title; ?>
                    
                    <?php 
                        $iconClass = '';
                        switch ($task_type) {
                            case 'Task':
                                $iconClass = 'document-outline';
                                break;
                            case 'Reminder':
                                $iconClass = 'calendar-clear-outline';
                                break;
                            case 'Announcement':
                                $iconClass = 'notifications-outline';
                                break;
                            default:
                                $iconClass = 'alert-circle-outline'; // Default icon for unknown type
                                break;
                        }
                    ?>
                    <div class="icon-circle">
                        <ion-icon class="icon1" name="<?php echo $iconClass; ?>"></ion-icon>
                    </div>
                </h2>
                <?php if (!empty($task_due_date)): ?>
                    <p class="taskDueDate">Due: <?php echo $formatted_due_date; ?></p>
                <?php endif; ?>
                <p style="font-size: 18px;"><?php echo $task_description; ?></p>
            </div>

            <!-- Uploaded Files and File Management -->
            <div class="row">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded mb-3" style="margin-left:50px;">
                        <h5 class="font-weight-bold mb-3">Your Output</h5>
                        
    
                        <!-- Display Uploaded Files -->
                        <?php if (!empty($uploaded_files)): ?>
                           
                            <ul>
                                <?php foreach ($uploaded_files as $file): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($file['uri']); ?>" target="_blank"><?php echo htmlspecialchars($file['name']); ?></a>
                                        <?php if (!$submitted): ?>
                                            <!-- Display bin icon for deletion -->
                                            <ion-icon class="bin-icon" name="trash-outline" onclick="deleteFile(<?php echo $file['id']; ?>)"></ion-icon>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <!-- File Upload Form -->
                        <?php if (!$submitted): ?>
                            <form id="fileUploadForm" action="taskdetails.php?task_id=<?php echo htmlspecialchars($_GET['task_id']); ?>" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="fileUpload">Upload File:</label>
                                    <input type="file" class="form-control-file" id="fileUpload" name="fileUpload[]" multiple>
                                </div>
                                <button type="submit" class="btn btn-primary" name="submit">Submit</button>
                            </form>
                        <?php endif; ?>

                        <!-- Unsubmit button -->
                        <?php if ($submitted): ?>
                            <form action="taskdetails.php?task_id=<?php echo htmlspecialchars($_GET['task_id']); ?>" method="post">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                                <button type="submit" class="btn btn-danger" name="unsubmit">Unsubmit</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded mb-3" style="margin-right:50px;">
                        <h5 class="font-weight-bold mb-3">Additional Detail 2</h5>
                        <p>Content for detail 2 goes here.</p>
                    </div>
                </div>
            </div>

            <!-- JavaScript to handle file deletion without page refresh -->
            <script>
    async function deleteFile(fileId) {
        if (!confirm("Are you sure you want to remove this file?")) {
            return; // If user cancels, do nothing
        }

        try {
            const formData = new FormData();
            formData.append('file_id', fileId);
            formData.append('delete', true);

            const response = await fetch(`taskdetails.php?task_id=<?php echo htmlspecialchars($_GET['task_id']); ?>`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Failed to delete file');
            }

            window.location.reload(); // Reload page after successful deletion
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the file.');
        }
    }
</script>



            <!-- Unsubmitted Files List -->
            <?php if (!$submitted && !empty($unsubmitted_files)): ?>
    <div class="p-3 bg-light rounded mb-3" style="margin-left:50px; margin-right:50px;">
        <h5 class="font-weight-bold mb-3">Unsubmitted Files</h5>
        <ul>
            <?php foreach ($unsubmitted_files as $file): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($file['uri']); ?>" target="_blank"><?php echo htmlspecialchars($file['name']); ?></a>
                    <!-- Display bin icon for deletion -->
                    <ion-icon class="bin-icon" name="trash-outline" onclick="deleteFile(<?php echo $file['id']; ?>)"></ion-icon>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


        </main>
        <!-- MAIN -->
    </section>
    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
