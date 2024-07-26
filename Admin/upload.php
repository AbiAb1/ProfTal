<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proftal";

// Log file path
$log_file = 'logfile.log';

// Function to write to log file
function write_log($message) {
    global $log_file;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Function to generate 6-digit random number
function generateRandomNumber() {
    return mt_rand(100000, 999999);
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    write_log("Connection failed: " . $conn->connect_error);
    $response = array("success" => false, "message" => "Connection failed.");
    echo json_encode($response);
    exit();
}

write_log("Database connected successfully.");

// Get data from form
$UserID = $_SESSION['user_id'];
$ContentID = $_POST['grade'];
$Type = 'Task';
$Title = $_POST['title'];
$DueDate = $_POST['due-date'];
$taskContent = $_POST['instructions'];
$timeStamp = date('Y-m-d H:i:s'); // Current timestamp

write_log("Received form data: UserID = $UserID, ContentID = $ContentID, Type = $Type, Title = $Title, DueDate = $DueDate, taskContent = $taskContent");

// Generate TaskID
$TaskID = generateRandomNumber();

// Handle multiple file uploads
$uploadOk = 1;
$target_dir = "../Attachments/";
$allFilesUploaded = true;

if (isset($_FILES['file'])) {
    $fileCount = count($_FILES['file']['name']);
    write_log("Number of files to upload: $fileCount");

    for ($i = 0; $i < $fileCount; $i++) {
        $fileTmpName = $_FILES['file']['tmp_name'][$i];
        $fileName = basename($_FILES['file']['name'][$i]);
        $target_file = $target_dir . $fileName;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $fileSize = $_FILES['file']['size'][$i];
        $fileMimeType = mime_content_type($fileTmpName);

        write_log("Processing file $fileName: Type = $fileType, Size = $fileSize, MimeType = $fileMimeType");

        // Check file size
        if ($fileSize > 5000000) { // Limit to 5MB
            write_log("File too large: $fileName");
            $allFilesUploaded = false;
            continue; // Skip to the next file
        }

        // Allow certain file formats
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
        if (!in_array($fileType, $allowedTypes)) {
            write_log("Invalid file type: $fileName");
            $allFilesUploaded = false;
            continue; // Skip to the next file
        }

        // Try to upload file
        if (move_uploaded_file($fileTmpName, $target_file)) {
            write_log("File uploaded: $fileName, Stored at: $target_file");

            // Prepare and bind for tasks table
            $sql = "INSERT INTO tasks (TaskID, ContentID, UserID, Type, Title, DueDate, taskContent) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $TaskID, $ContentID, $UserID, $Type, $Title, $DueDate, $taskContent);

            // Log SQL query and bound parameters
            write_log("Preparing SQL query: $sql");
            write_log("Bound parameters: TaskID = $TaskID, ContentID = $ContentID, UserID = $UserID, Type = $Type, Title = $Title, DueDate = $DueDate, taskContent = $taskContent");

            // Execute the statement for tasks table
            if ($stmt->execute()) {
                write_log("Task added with ID: $TaskID, UserID: $UserID, ContentID: $ContentID");

                // Get user name from useracc table
                $userQuery = $conn->prepare("SELECT fname FROM useracc WHERE UserID = ?");
                $userQuery->bind_param("s", $UserID);
                $userQuery->execute();
                $userResult = $userQuery->get_result();
                $userName = $userResult->fetch_assoc()['fname'];
                write_log("Fetched user name: $userName for UserID: $UserID");

                // Get content title from feedcontent table
                $contentQuery = $conn->prepare("SELECT Title FROM feedcontent WHERE ContentID = ?");
                $contentQuery->bind_param("s", $ContentID);
                $contentQuery->execute();
                $contentResult = $contentQuery->get_result();
                $contentTitle = $contentResult->fetch_assoc()['Title'];
                write_log("Fetched content title: $contentTitle for ContentID: $ContentID");

                // Prepare notification title
                $notificationTitle = "$userName Posted a new $Type! ($contentTitle)";
                $notificationContent = "$Title: $taskContent";

                // Insert into notifications table
                $notifStmt = $conn->prepare("INSERT INTO notifications (UserID, TaskID, ContentID, Title, Content, Status) VALUES (?, ?, ?, ?, ?, ?)");
                $status = 1; // Setting status to 1
                $notifStmt->bind_param("sssssi", $UserID, $TaskID, $ContentID, $notificationTitle, $notificationContent, $status);

                // Log SQL query and bound parameters for notifications
                $notifSql = "INSERT INTO notifications (UserID, TaskID, ContentID, Title, Content, Status) VALUES (?, ?, ?, ?, ?, ?)";
                write_log("Preparing SQL query for notifications: $notifSql");
                write_log("Bound parameters for notifications: UserID = $UserID, TaskID = $TaskID, ContentID = $ContentID, Title = $notificationTitle, Content = $notificationContent, Status = $status");

                // Execute the statement for notifications table
                if ($notifStmt->execute()) {
                    write_log("Notification added for TaskID $TaskID, Title: $notificationTitle");

                    // Insert into docu table
                    $timestamp = date("Y-m-d H:i:s");
                    $docuStmt = $conn->prepare("INSERT INTO docu (UserID, ContentID, TaskID, name, mimeType, size, uri, Status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $docuStmt->bind_param("sssssssis", $UserID, $ContentID, $TaskID, $fileName, $fileMimeType, $fileSize, $target_file, $status, $timestamp);

                    // Log SQL query and bound parameters for docu
                    $docuSql = "INSERT INTO docu (UserID, ContentID, TaskID, name, mimeType, size, uri, Status, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    write_log("Preparing SQL query for docu: $docuSql");
                    write_log("Bound parameters for docu: UserID = $UserID, ContentID = $ContentID, TaskID = $TaskID, name = $fileName, mimeType = $fileMimeType, size = $fileSize, uri = $target_file, Status = $status, timestamp = $timestamp");

                    // Execute the statement for docu table
                    if ($docuStmt->execute()) {
                        write_log("Document record added: FileName $fileName, TaskID $TaskID, Path: $target_file");
                    } else {
                        write_log("Error inserting into docu: " . $docuStmt->error);
                    }

                    // Close docu statement
                    $docuStmt->close();
                } else {
                    write_log("Error inserting into notifications: " . $notifStmt->error);
                }

                // Close notification statement
                $notifStmt->close();
            } else {
                write_log("Error inserting into tasks: " . $stmt->error);
                $allFilesUploaded = false; // If task creation fails, the overall process should be marked as failed
            }

            // Close statements
            $stmt->close();
            $userQuery->close();
            $contentQuery->close();
        } else {
            write_log("Error uploading file: $fileName");
            $allFilesUploaded = false; // If file upload fails, the overall process should be marked as failed
        }
    }
}

// Set response based on upload success
if ($allFilesUploaded) {
    $response = array("success" => true, "message" => "Task created successfully.");
} else {
    $response = array("success" => false, "message" => "Error creating task. Some files may not have been uploaded.");
}

echo json_encode($response);

// Close connection
$conn->close();
write_log("Database connection closed.");
?>
