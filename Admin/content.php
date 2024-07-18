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
            background-color: #E8E8E8;
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
        .tasktitle {
            color: black;
            transition: color 0.3s ease; /* Smooth transition */
        }
        .tasktitle:hover {
            color: #9B2035; /* Hover color */
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
            <h1 class="title">Content</h1>

            <?php
            // Retrieve query parameters
            $grade = isset($_GET['grade']) ? htmlspecialchars($_GET['grade']) : 'N/A';
            $section = isset($_GET['section']) ? htmlspecialchars($_GET['section']) : 'N/A';

            // Display grade and section in contentCard
            ?>
            <!-- Content Details -->
            <div class="contentCard">
                <h2><?php echo $grade; ?></h2>
                <p>Section: <?php echo $section; ?></p>
            </div>

            <!-- Tasks List -->
            <div class="taskList">
                <?php
                // Fetch and display tasks here
                while ($row_task = mysqli_fetch_assoc($result_tasks)) {
                    $taskID = $row_task['TaskID'];
                    $taskTitle = $row_task['Title'];
                    $taskTimestamp = $row_task['TimeStamp'];
                    $taskType = $row_task['Type'];
                    $iconClass = '';

                    // Determine the task type and display corresponding icon
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
                ?>
                    <!-- Task Container -->
                    <a href='taskdetails.php?task_id=<?php echo htmlspecialchars($taskID); ?>&content_id=<?php echo htmlspecialchars($content_id); ?>' class='taskLink'>
                        <div class='taskContainer'>
                            <h3 class='tasktitle'><?php echo htmlspecialchars($taskTitle); ?></h3>
                            <p><?php echo htmlspecialchars($taskTimestamp); ?></p>
                            <div class='taskIcon'>
                                <ion-icon name='<?php echo htmlspecialchars($iconClass); ?>'></ion-icon>
                            </div>
                        </div>
                    </a>
                <?php
                }
                ?>
            </div>
        </main>
        <!-- MAIN -->
    </section>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
