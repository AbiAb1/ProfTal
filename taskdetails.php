<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include your database connection file here
include 'connection.php';
include 'fetch_files.php'; // Include the fetchFiles function

// Initialize variables
$task_title = "";
$task_description = "";
$task_type = "";
$task_due_date = "";
$task_status = 0; // Initialize task status
$user_fullname = ""; // Initialize user's full name

// Check if task_id is provided in URL
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Query to fetch task details based on TaskID and join with useracc to get user's full name
    $sql_task_details = "SELECT tasks.*, useracc.fname, useracc.lname 
                         FROM tasks 
                         LEFT JOIN useracc ON tasks.UserID = useracc.UserID
                         WHERE tasks.TaskID = '$task_id' ";
    $result_task_details = mysqli_query($conn, $sql_task_details);

    // Check if task details are found
    if (mysqli_num_rows($result_task_details) > 0) {
        $row_task_details = mysqli_fetch_assoc($result_task_details);
        $task_title = $row_task_details['Title'];
        $task_description = $row_task_details['taskContent'];
        $task_type = $row_task_details['Type'];
        $task_due_date = $row_task_details['Duedate']; // Assuming DueDate is the column name
        $user_fullname = $row_task_details['fname'] . ' ' . $row_task_details['lname']; // Concatenate fname and lname
    } else {
        echo "Task details not found.";
        exit(); // Exit if task details are not found
    }

    // Query to fetch task status from documents table
    $sql_task_status = "SELECT status FROM documents WHERE TaskID = '$task_id' AND UserID = '$user_id'";
    $result_task_status = mysqli_query($conn, $sql_task_status);

    // Check if task status is found
    if (mysqli_num_rows($result_task_status) > 0) {
        $row_task_status = mysqli_fetch_assoc($result_task_status);
        $task_status = $row_task_status['status']; // Get the task status
    }
} else {
    echo "Task ID not provided.";   
    exit(); // Exit if task ID is not provided
}

// Format Due Date
$formatted_due_date = date('F j, Y \a\t g:i A', strtotime($task_due_date));

// Determine if due date is today, future, or past due
$due_date_timestamp = strtotime($task_due_date);
$today_timestamp = strtotime('today');
$label = '';
if ($due_date_timestamp > $today_timestamp) {
    $label = 'Assigned';
} elseif ($due_date_timestamp === $today_timestamp) {
    $label = 'Assigned';
} else {
    $label = 'Missing'; // Due date has passed
}

// Fetch documents associated with the task
$sql_documents = "SELECT ID, name FROM docu WHERE TaskID = '$task_id'";
$result_documents = mysqli_query($conn, $sql_documents);
$documents = [];
if (mysqli_num_rows($result_documents) > 0) {
    while ($row = mysqli_fetch_assoc($result_documents)) {
        $documents[] = $row;
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
            width:600px;
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
        .message-container {
    
        align-items: flex-start;
        justify-content: flex-end;
        margin-bottom: 5px;
        position: relative;
    }

    .message {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        margin-bottom: 10px;
        position: relative; /* To position profile pic */
        padding: 10px;
        background-color: #f9f9f9; /* Light background for messages */
        border-radius: 8px;
        padding-right:70px;
    }

    .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background-color: #ccc; /* Placeholder color if image doesn't load */
    }

    /* Ensure specificity */
    .message .user-name {
        font-weight: bold;
        margin-bottom: 5px;
        color: black; /* Set text color to black */
    }



    .message-text {
        margin-bottom: 10px;
        word-wrap: break-word; /* Handle long words */
    }

    .incoming {
        background-color: transparent;
    }

    .outgoing {
        background-color: transparent;
    }


    .submit-button {
        margin-top: 10px;
    }
/* Container for attachments */
.Attachment-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Space between items */
    max-width: 100%; /* Adjust as needed */
    height:70px;
    margin-top:30px;
    margin-bottom:50px;
}

/* Individual attachment item */
.file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: calc(50% - 10px); /* Two items per row, adjust for gaps */
    padding: 10px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: #333;
    overflow: hidden; /* To handle overflow issues */
}

/* File name */
.file span {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    flex: 1;
    padding-right: 10px; /* Space for the pin icon */
}

/* Pin icon container */
.file .pin-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #9b2035;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Pin icon styles */
.file .pin-icon i {
    font-size: 16px;
    color: #fff;
}



    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <?php include 'navbar.php'; ?>
    </section>
    <!-- SIDEBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include 'topbar.php'; ?>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            
            <h1 class="title">Task Details</h1>
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
                <?php if (!empty($task_due_date) && $task_type === 'Task'): ?>
                    <p class="taskDueDate">Due: <?php echo $formatted_due_date; ?></p>
                <?php endif; ?>

                <?php if ($label === 'Assigned' && $task_type === 'Task'): ?>
                    <span class="status-label assigned"><?php echo $label; ?></span>
                <?php elseif ($label === 'Missing' && $task_type === 'Task'): ?>
                    <span class="status-label missing"><?php echo $label; ?></span>
                <?php endif; ?>           
                    <p style="font-size: 18px;"><?php echo $task_description; ?></p>
                
                <h6 style ="margin-top:50px;">Attachments:</h6>
                <?php if (!empty($documents)): ?>
                    <div class="Attachment-container">
                        <?php foreach ($documents as $document): ?>
                            <a href="attachments/<?php echo $document['name']; ?>" download class="file">
                                <span><?php echo $document['name']; ?></span>
                                <div class="pin-icon">
                                    <i class="bx bx-paperclip"></i> <!-- Replace with your pin icon class -->
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php else: ?>
                        <p>No documents found.</p>
                    <?php endif; ?>
            </div>

            <?php if ($task_type === 'Task'): ?>
            <div class="row" style="margin-top: 50px; margin-left:100px;margin-right:100px;">
                <div class="col-md-7">
                    <div class="p-3 bg-light rounded mb-3 file-upload">
                        <h5 class="font-weight-bold mb-3 output-title">Your Output</h5>
                        <p class="text-muted center-message" id="no-file-message">To attach a file click here, or click the plus icon.</p>
                        <ion-icon id="plusicon"class="plus-icon" name="add-outline"onclick="openFileExplorer()"></ion-icon>
                        <form id="uploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                            <input type="hidden" name="content_id" value="<?php echo isset($_GET['content_id']) ? htmlspecialchars($_GET['content_id']) : ''; ?>">
                            <input type="file" name="files[]" id="fileInput" multiple style="display:none;" onchange="handleFiles(this.files)" />
                            <div class="file-container" id="fileContainer"></div>
                            <button type="submit" id="submitButton" class="btn submit-button" disabled>Upload</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="p-3 bg-light rounded mb-3">
                        <h5 class="font-weight-bold mb-3">Private Message -> <?php echo htmlspecialchars($user_fullname); ?></h5>
                        <form id="messageForm">
                            <div class="message-container">
                                <input type="hidden" name="content_id" value="<?php echo isset($_GET['content_id']) ? htmlspecialchars($_GET['content_id']) : ''; ?>" />
                                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
                                <input type="hidden" name="outgoing_id" id="outgoing_id" />
                                <input type="text" class="form-control" name="message" placeholder="Type your message here..." required />
                                <button type="submit" class="btn submit-button">Send</button>
                            </div>
                        </form>
                        <div id="responseMessage"></div>
                        <div id="conversationMessages" class="mt-3"></div>
                    </div>
                </div>



            </div>
            <?php endif; ?>

        </main>
        <!-- MAIN -->
    </section>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch outgoing_id based on task_id
        const task_id = <?php echo json_encode($task_id); ?>;
        fetch('get_outgoing_id.php?task_id=' + task_id)
            .then(response => response.json())
            .then(data => {
                if (data.outgoing_id) {
                    document.getElementById('outgoing_id').value = data.outgoing_id;
                    fetchConversationMessages(); // Fetch messages after getting outgoing_id
                } else {
                    console.error('Outgoing ID not found:', data.error);
                }
            })
            .catch(error => {
                console.error('Error fetching outgoing_id:', error);
            });

        document.getElementById('messageForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the traditional way

            var formData = new FormData(this);

            fetch('insert_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('responseMessage').innerText = data;
                fetchConversationMessages();
                this.reset(); // Clear the form
            })
            .catch(error => {
                document.getElementById('responseMessage').innerText = 'Error: ' + error;
            });
        });
    });

    function fetchConversationMessages() {
    const content_id = document.querySelector('input[name="content_id"]').value;
    const task_id = document.querySelector('input[name="task_id"]').value;

    fetch(`get_conversation.php?content_id=${content_id}&task_id=${task_id}`)
        .then(response => response.json())
        .then(data => {
            const conversationMessages = document.getElementById('conversationMessages');
            conversationMessages.innerHTML = '';
            if (data.error) {
                conversationMessages.innerText = data.error;
            } else {
                data.messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('message');

                    // Create and append user name
                    const userName = document.createElement('p');
                    userName.classList.add('user-name');
                    userName.innerText = message.FullName;

                    // Create and append message text
                    const messageText = document.createElement('p');
                    messageText.classList.add('message-text');
                    messageText.innerText = message.Comment;

                    // Create and append profile picture
                    const profilePic = document.createElement('img');
                    profilePic.src = message.ProfilePic; // Path set by PHP script
                    profilePic.alt = `${message.FullName}'s profile picture`;
                    profilePic.classList.add('profile-pic');

                    // Append elements to messageElement
                    messageElement.appendChild(userName);
                    messageElement.appendChild(messageText);
                    messageElement.appendChild(profilePic);

                    // Add appropriate class for message type
                    if (message.Incoming_id == <?php echo json_encode($_SESSION['user_id']); ?>) {
                        messageElement.classList.add('incoming');
                    } else {
                        messageElement.classList.add('outgoing');
                    }
                    conversationMessages.appendChild(messageElement);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching conversation messages:', error);
        });
}



</script>









    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var fileInput = document.getElementById('fileInput');
            var submitButton = document.getElementById('submitButton');
            var fileContainer = document.getElementById('fileContainer');
            var noFileMessage = document.getElementById('no-file-message');
            var uploadForm = document.getElementById('uploadForm');
            var plusicon = document.getElementById('plusicon');
            



            // Function to handle file selection
            window.handleFiles = function (files) {
                if (files.length > 0) {
                    submitButton.disabled = false;
                    noFileMessage.style.display = 'none';
                    Array.from(files).forEach(file => {
                        var fileDiv = document.createElement('div');
                        fileDiv.classList.add('file');
                        fileDiv.innerHTML = `<span>${file.name}</span><i class="bx bx-x remove-file" onclick="removeFile(this)"></i>`;
                        fileContainer.appendChild(fileDiv);
                    });
                } else {
                    submitButton.disabled = true;
                    noFileMessage.style.display = 'block';
                }
            };

            // Function to open file explorer
            window.openFileExplorer = function () {
                fileInput.click();
            };

            // Function to handle file removal
            window.removeFile = function (icon) {
                var fileDiv = icon.parentNode;
                fileDiv.parentNode.removeChild(fileDiv);
                if (fileContainer.children.length === 0) {
                    noFileMessage.style.display = 'block';
                }
            };
            
            
            // Function to remove file from server
            window.removeFileFromServer = function (fileId, icon) {
                fetch('remove_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'file_id=' + fileId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var fileDiv = icon.parentNode;
                        fileDiv.parentNode.removeChild(fileDiv);
                        if (fileContainer.children.length === 0) {
                            noFileMessage.style.display = 'block';
                        }
                    } else {
                        console.error(data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            };

            // Handle form submission via AJAX
            uploadForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(uploadForm);

                // Fetch existing file IDs first
                fetch('fetch_existing_files.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Append existing file IDs to formData
                        if (data.existing_files && data.existing_files.length > 0) {
                            data.existing_files.forEach(fileId => {
                                formData.append('existing_file_ids[]', fileId);
                            });
                        }

                        // Now proceed to upload all files
                        fetch('upload.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(uploadData => {
                            if (uploadData.success) {
                                // Fetch and display updated file list (if needed)
                                fetchFiles(); // Replace with your function to update UI or fetch new data
                                Swal.fire({
                                icon: 'success',
                                title: 'Files Uploaded Successfully!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload(); // Refresh the page
                            });
                        }  else {
                                console.error(uploadData.error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Upload Error',
                                    text: uploadData.error
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Error',
                                text: 'Failed to upload files. Please try again.'
                            });
                        });
                    } else {
                        console.error(data.error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Fetch Error',
                            text: 'Failed to fetch existing files. Please try again.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching existing files:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Fetch Error',
                        text: 'Failed to fetch existing files. Please try again.'
                    });
                });
            });

            // Function to fetch and display files
            function fetchFiles() {
                fetch('get_files.php?task_id=<?php echo $task_id; ?>&content_id=<?php echo isset($_GET['content_id']) ? htmlspecialchars($_GET['content_id']) : ''; ?>')
                .then(response => response.json())
                .then(data => {
                    fileContainer.innerHTML = '';
                    if (data.length > 0) {
                        noFileMessage.style.display = 'none';
                        data.forEach(file => {
                            var fileDiv = document.createElement('div');
                            fileDiv.classList.add('file');

                            // Create a clickable link to open the file in a new window/tab
                            var fileLink = document.createElement('a');
                            fileLink.textContent = file.name;
                            fileLink.href = 'Documents/' + file.name; // Replace with actual path to view file

                            // Check file extension to determine behavior
                            var fileExtension = file.name.split('.').pop().toLowerCase();
                            if (fileExtension === 'pdf') {
                                fileLink.target = '_blank'; // Open PDF in a new tab/window
                            } else {
                                // Open other file types directly in the same window
                                fileLink.target = '_self'; // or remove this line to use default behavior
                            }

                            // Append the link to fileDiv
                            fileDiv.appendChild(fileLink);

                            // Add remove file icon
                            if (<?php echo $task_status; ?> !== 1) {
                                var removeIcon = document.createElement('i');
                                removeIcon.classList.add('bx', 'bx-x', 'remove-file');
                                removeIcon.setAttribute('onclick', `removeFileFromServer(${file.id}, this)`);
                                fileDiv.appendChild(removeIcon);
                            }
                            // Append fileDiv to fileContainer
                            fileContainer.appendChild(fileDiv);
                        });
                    } else {
                        noFileMessage.style.display = 'block';
                    }

                    // Update button text based on task status
                     // Update button text based on task status
                     const taskStatus = <?php echo $task_status; ?>;

                    if (taskStatus === 1) {
                        submitButton.textContent = 'Unsubmit';
                        submitButton.disabled = false;
                        plusicon.style.color = '#888'; // Dark gray color
                        plusicon.style.pointerEvents = 'none'; // Disable pointer events
                    } else {
                        submitButton.textContent = 'Upload';
                        plusicon.style.color = ''; // Reset to default color (if any)
                        plusicon.style.pointerEvents = ''; // Enable pointer events
                    }
                });
            }
            

            // Function to handle "Unsubmit" action
            submitButton.addEventListener('click', function () {
                var taskID = '<?php echo $task_id; ?>';
                var contentID = '<?php echo isset($_GET['content_id']) ? htmlspecialchars($_GET['content_id']) : ''; ?>';

                fetch('updateTaskStatus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        taskID: taskID,
                        contentID: contentID,
                        status: 0 // Set status to 0 for unsubmit
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success alert with SweetAlert
                        Swal.fire({
                        icon: 'success',
                        title: 'Task has been unsubmitted successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload(); // Refresh the page after showing success alert
                    });

                        // Update button text and disable it after unsubmit
                        submitButton.textContent = 'Upload';
                        submitButton.disabled = true;
                    } else {
                        // Show error alert with SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error unsubmitting task',
                            text: data.message || 'An error occurred. Please try again.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Show error alert with SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error unsubmitting task',
                        text: 'An error occurred. Please try again.'
                    });
                });
            });

            

            // Fetch existing files on page load
            fetchFiles();
        });
    </script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
       
        
    </script>

</body>
</html>
