
<?php

// Include your database connection file here
include 'connection.php';

// Initialize variables
$content_id = null;
$content_title = "";
$content_captions = "";

// Check if content_id is provided in URL
if (isset($_GET['ContentID'])) {
    $content_id = $_GET['ContentID'];

    // Query to fetch tasks based on ContentID
    $sql_tasks = "SELECT * FROM tasks WHERE ContentID = '$content_id' ORDER BY Timestamp DESC";
    $result_tasks = mysqli_query($conn, $sql_tasks);

    // Query to fetch content details
    $sql_content = "SELECT Title, Captions FROM feedcontent WHERE ContentID = '$content_id'";
    $result_content = mysqli_query($conn, $sql_content);

    // Check if there are any tasks
    if (mysqli_num_rows($result_tasks) > 0) {
        // Fetch content details
        if ($row_content = mysqli_fetch_assoc($result_content)) {
            $content_title = $row_content['Title'];
            $content_captions = $row_content['Captions'];
        }
        // Start HTML output
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tasks</title>
            <!-- ======= Styles ====== -->
            <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
            <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.css" rel="stylesheet">

            <link rel="stylesheet" href="assets/css/styles.css">
            <style>
                .taskList {
                    list-style-type: none;
                    padding: 0;
                }
                .taskList li {
                    margin-bottom: 10px;
                    padding: 10px;
                    background-color: #f0f0f0;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                .contentCard {
                    background-color: var(--blue); /* Adjust color as needed */
                    border-radius: 12px;
                    padding: 20px;
                    margin-bottom: 20px;
                    color: #fff;
                    margin-left: 50px; /* Margin only on the left */
                    margin-right: 50px; /* Margin only on the right */
                    height: 300px;
                }

                .contentCard h2 {
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                .contentCard p {
                    font-size: 16px;
                    line-height: 1.6;
                }
                .taskContainer {
                    background-color: #f0f0f0;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 15px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    margin-left: 50px; /* Margin only on the left */
                    margin-right: 50px; /* Margin only on the right */
                    position: relative;
                    height: 100px;
                }
                .taskContainer h3 {
                    font-size: 20px;
                    margin-bottom: 5px;
                }
                .taskContainer p {
                    font-size: 14px;
                    color: #666;
                }
                .taskIcon {
                    position: absolute;
                    top: 50%;
                    right: -25px; /* Adjust distance from the right */
                    transform: translateY(-50%);
                    width: 60px;
                    height: 60px;
                    background-color: #9B2035; /* Adjust color as needed */
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin-right: 50px;
                }
                .taskIcon ion-icon {
                    color: #fff;
                    font-size: 25px;
                    
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
                        <h1 style="padding-left:50px; margin-bottom: 20px;">Tasks</h1>
                        <!-- ======================= Content Details ================== -->
                        <div class="contentCard">
                            <h2><?php echo $content_title; ?></h2>
                            <p><?php echo $content_captions; ?></p>
                        </div>

                        <!-- ======================= Tasks List ================== -->
                        <div class="taskList">
                            <?php
                            // Output tasks
                            while ($row_task = mysqli_fetch_assoc($result_tasks)) {
                                $taskID = $row_task['TaskID'];
                                echo "<a href='taskdetails.php?task_id=$taskID' class='taskLink'>";
                                echo "<div class='taskContainer'>";
                                echo "<h3>" . $row_task['Title'] . "</h3>";
                                echo "<p>" . $row_task['TimeStamp'] . "</p>";
                                // Determine the task type and display corresponding icon
                                $taskType = $row_task['Type'];
                                $iconClass = '';
                                switch ($taskType) {
                                    case 'Task':
                                        $iconClass = 'document-outline'; // Adjust ion-icon name as needed
                                        break;
                                    case 'Reminder':
                                        $iconClass = 'calendar-clear-outline'; // Adjust ion-icon name as needed
                                        break;
                                    case 'Announcement':
                                        $iconClass = 'notifications-outline'; // Default icon for unknown type
                                        break;
                                }
                                echo "<div class='taskIcon'><ion-icon name='$iconClass'></ion-icon></div>";
                                
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </main>
                    <!-- MAIN -->
                </section>



                
                <!-- =========== Scripts =========  -->
                <script src="assets/js/script.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
                <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

            </body>

        </html>
        <?php
    } else {
        echo "No content available for this content ID.";
    }
} else {
    echo "Content ID not provided.";
}

// Close database connection
mysqli_close($conn);
?>