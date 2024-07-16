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

// Check if task_id is provided in URL
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Query to fetch task details based on TaskID
    $sql_task_details = "SELECT * FROM tasks WHERE TaskID = '$task_id'";
    $result_task_details = mysqli_query($conn, $sql_task_details);

    // Check if task details are found
    if (mysqli_num_rows($result_task_details) > 0) {
        $row_task_details = mysqli_fetch_assoc($result_task_details);
        $task_title = $row_task_details['Title'];
        $task_description = $row_task_details['taskContent'];
        $task_type = $row_task_details['Type'];
        $task_due_date = $row_task_details['Duedate']; // Assuming DueDate is the column name
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
            margin-bottom: 50px; /* Adjusted from 50px to 20px */
            font-size: 14px; /* Adjusted font size */
            color: #999; /* Adjusted color */
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

            <!-- First Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded mb-3" style="margin-left:50px;">
                        <h5 class="font-weight-bold mb-3">Additional Detail 1</h5>
                        <p class="text-muted">Content for detail 1 goes here.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded mb-3" style="margin-right:50px;">
                        <h5 class="font-weight-bold mb-3">Additional Detail 2</h5>
                        <p class="text-muted">Content for detail 2 goes here.</p>
                    </div>
                </div>
            </div>

           
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
</body>
</html>
