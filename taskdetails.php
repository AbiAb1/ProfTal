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
<style>
        .taskDetails {
            padding: 20px;
            border-radius: 8px;
            margin-left: 50px; /* Margin only on the left */
            margin-right: 50px; /* Margin only on the right */
            position: relative; /* Added for positioning the label */
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
            margin-bottom: 50px; /* Adjusted from 50px to 20px */
            font-size: 14px; /* Adjusted font size */
            color: #999; /* Adjusted color */
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%; /* Make it full width */
            max-height: 100%;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 8px;
        }
        .file-upload input[type="file"] {
            display: none; /* Hide the file input */
        }
        .plus-icon {
            font-size: 30px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            color: #9B2035;
        }
        .center-message {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            margin: 50px;
        }
        .submit-button {
            display: block;
            width: 100%;
            margin-top: 10px;
            background-color: #9B2035;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            transition: color 0.3s ease; /* Smooth transition */
        }

        .submit-button:hover {
            background-color: #6f1626; /* Hover color */
            color:#fff;
        }
        .submit-button[disabled] {
            background-color: #ddd; /* Grey out when disabled */
            color: #666;
            cursor: not-allowed;
        }
        .status-label {
            position: absolute;
            top: 0;
            right: 0;
            margin: 25px;
            padding: 5px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 100px;
        }
        .status-label.assigned {
            color: green;
        }
        .status-label.missing {
            color: red;
        }
        .file-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            width: 100%;
        }
        .file-container .file {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative; /* Needed for the remove button */
        }
        .file-container .file span {
            font-size: 14px;
            color: #333;
            flex-grow: 1;
            text-align: left;
        }
        .remove-file {
            cursor: pointer;
            color: red;
            font-size: 20px;
            position: absolute; /* Positioning the remove button */
            right: 10px; /* Adjust as needed */
            top: 50%;
            transform: translateY(-50%);
        }
        .output-title {
    font-weight: bold;
    margin-bottom: 10px;
    align-self: flex-start; /* Aligns the title to the leftmost side */
}
    </style>
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
                <div class="col-md-7">
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
                <div class="col-md-5">
                    <div class="p-3 bg-light rounded mb-3">
                        <h5 class="font-weight-bold mb-3">Private Message</h5>
                        <p class="text-muted">Content for detail 2 goes here.</p>
                        <div class="message-container">
                            <input type="text" class="form-control" id="messageInput" placeholder="Type your message here..." />
                            <button class="btn submit-button" id="sendButton">Send</button>
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
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
   document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileContainer = document.getElementById('fileContainer');
    const plusIcon = document.querySelector('.plus-icon');
    let files = [];

    // Plus icon click handler
    plusIcon.addEventListener('click', function(event) {
        fileInput.click(); // Simulate a click on the file input
        event.stopPropagation(); // Prevent the event from bubbling up
    });

    // File input change event listener
    fileInput.addEventListener('change', function(event) {
        const selectedFiles = Array.from(event.target.files);
        files = files.concat(selectedFiles);

        renderFileList();
    });

    fileContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-file')) {
            const index = event.target.getAttribute('data-index');
            files.splice(index, 1);
            renderFileList();
            event.stopPropagation(); // Prevent reopening the file explorer
        }
    });

    function renderFileList() {
        fileContainer.innerHTML = ''; // Clear the container

        if (files.length > 0) {
            document.getElementById('no-file-message').style.display = 'none';
            document.getElementById('submitButton').disabled = false;

            files.forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.classList.add('file');
                fileDiv.innerHTML = `
                    <span>${file.name}</span>
                    <ion-icon class="remove-file" name="close-circle-outline" data-index="${index}"></ion-icon>
                `;
                fileContainer.appendChild(fileDiv);
            });
        } else {
            document.getElementById('no-file-message').style.display = 'block';
            document.getElementById('submitButton').disabled = true;
        }
    }
});

</script>

</body>
</html>
