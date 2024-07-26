<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'connection.php'; // Assuming this file contains your database connection code

// Fetch tasks from the database
$user_id = $_SESSION['user_id'];

// Query to fetch contents from feedcontent table
$sql = "SELECT ts.TaskID, ts.ContentID, ts.Type, ts.Title, ts.Duedate, ts.taskContent, ts.TimeStamp
        FROM tasks ts
        INNER JOIN usercontent uc ON ts.ContentID = uc.ContentID
        WHERE uc.UserID = $user_id AND Status=1 AND ts.Type='Task'";
$result = $conn->query($sql);

// Initialize arrays to categorize tasks
$todayTasks = [];
$comingUpTasks = [];
$laterTasks = [];
$noDueTasks = [];
$missingTasks = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskDueDate = strtotime($row['Duedate']);
        $today = strtotime('today');
        $oneWeekFromToday = strtotime('+1 week');
        $threeWeeksFromToday = strtotime('+3 weeks');

        if ($taskDueDate === false) {
            $noDueTasks[] = $row;
        } elseif ($taskDueDate < $today) {
            $missingTasks[] = $row;
        } elseif ($taskDueDate <= $today) {
            $todayTasks[] = $row;
        } elseif ($taskDueDate <= $oneWeekFromToday) {
            $comingUpTasks[] = $row;
        } else {
            $laterTasks[] = $row;
        }
    }
} else {
    echo "No tasks found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Optional styles for button active state */
        .button-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px; /* Adjust as needed */
            font-weight:bold;
        }

        .section-button {
            display: inline-block;
            margin: 0 10px; /* Adjust spacing */
            text-decoration: none; /* Remove default underline */
            color: #333; /* Text color */
            cursor: pointer;
            position: relative; /* Ensure position for pseudo-element */
            padding: 5px 10px; /* Adjust padding for button size */
        }

        .section-button.active::after {
            content: ''; /* Required for pseudo-element */
            position: absolute; /* Position relative to the button */
            left: 0;
            right: 0;
            bottom: -2px; /* Adjust as needed */
            height: 1px; /* Underline height */
            background-color: #9B2035; /* Underline color */
        }

        .task-container {
            background-color: #E8E8E8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-left: 100px; /* Margin only on the left */
            margin-right: 100px; /* Margin only on the right */
            position: relative;
            height: 100px;
        }

        .category-title {
            margin-top: 20px;
            font-size: 1.2em;
            margin-left: 100px;
            margin-right: 100px;
            margin-bottom: 20px;
        }

        .task-title {
            color: black;
            transition: color 0.3s ease; /* Smooth transition */
        }

        .task-title:hover {
            color: #9B2035; /* Hover color */
        }

        .task-icon {
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

        .task-icon ion-icon {
            color: #fff;
            font-size: 25px;
        }
    </style>
    <title>To-Do's</title>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <?php include 'navbar.php'; ?>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include 'topbar.php'; ?>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <h1 class="title">To-Do's</h1>

            <!-- Clickable buttons within the main content area -->
            <div class="button-container">
                <a href="#" class="section-button <?= empty($_GET['section']) || $_GET['section'] === 'assigned' ? 'active' : ''; ?>" data-section="assigned">Assigned</a>
                <a href="#" class="section-button <?= empty($_GET['section']) || $_GET['section'] === 'missing' ? 'active' : ''; ?>" data-section="missing">Missing</a>            
            </div>

            <!-- Assigned section content -->
            <div id="assigned-content" style="display: block;">
                <h2 class="category-title">Today</h2>
                <?php foreach ($todayTasks as $task) : ?>
                    <div class="task-container">
                    <a href='taskdetails.php?task_id=<?= $task['TaskID']; ?>&content_id=<?= $task['ContentID']; ?>&user_id=<?= $user_id; ?>' class='taskLink'>
                            
                                <h3 class='task-title'><?= $task['Title']; ?></h3>
                                <p>Due Date: <?= $task['Duedate']; ?></p>
                                <div class='task-icon'>
                                    <ion-icon name='document-outline'></ion-icon>
                                </div>
                            
                        </a>
                    </div>
                <?php endforeach; ?>

                <h2 class="category-title">Coming Up</h2>
                <?php foreach ($comingUpTasks as $task) : ?>
                    <div class="task-container">
                    <a href='taskdetails.php?task_id=<?= $task['TaskID']; ?>&content_id=<?= $task['ContentID']; ?>&user_id=<?= $user_id; ?>' class='taskLink'>
                            
                                <h3 class='task-title'><?= $task['Title']; ?></h3>
                                <p>Due Date: <?= $task['Duedate']; ?></p>
                                <div class='task-icon'>
                                    <ion-icon name='document-outline'></ion-icon>
                                </div>
                            
                        </a>
                    </div>
                <?php endforeach; ?>

                <h2 class="category-title">Later</h2>
                <?php foreach ($laterTasks as $task) : ?>
                    <div class="task-container">
                    <a href='taskdetails.php?task_id=<?= $task['TaskID']; ?>&content_id=<?= $task['ContentID']; ?>&user_id=<?= $user_id; ?>' class='taskLink'>                            
                                <h3 class='task-title'><?= $task['Title']; ?></h3>
                                <p>Due Date: <?= $task['Duedate']; ?></p>
                                <div class='task-icon'>
                                    <ion-icon name='document-outline'></ion-icon>
                                </div>
                            
                        </a>
                    </div>
                <?php endforeach; ?>

                <h2 class="category-title">No Due Date</h2>
                <?php foreach ($noDueTasks as $task) : ?>
                    <div class="task-container">
                    <a href='taskdetails.php?task_id=<?= $task['TaskID']; ?>&content_id=<?= $task['ContentID']; ?>&user_id=<?= $user_id; ?>' class='taskLink'>                           
                                <h3 class='task-title'><?= $task['Title']; ?></h3>
                                <p>Due Date: <?= $task['Duedate']; ?></p>
                                <div class='task-icon'>
                                    <ion-icon name='document-outline'></ion-icon>
                                </div>
                            
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- End of assigned section content -->

            <!-- Missing section content -->
            <div id="missing-content" style="<?= isset($_GET['section']) && $_GET['section'] === 'missing' ? 'display: block;' : 'display: none;'; ?>">
                <h2 class="category-title">Missing</h2>
                <?php foreach ($missingTasks as $task) : ?>
                    <div class="task-container">
                    <a href='taskdetails.php?task_id=<?= $task['TaskID']; ?>&content_id=<?= $task['ContentID']; ?>&user_id=<?= $user_id; ?>' class='taskLink'>                            
                                <h3 class='task-title'><?= $task['Title']; ?></h3>
                                <p>Due Date: <?= $task['Duedate']; ?></p>
                                <div class='task-icon'>
                                    <ion-icon name='document-outline'></ion-icon>
                                </div>
                       
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- End of missing section content -->

        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        // JavaScript for handling section switching
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.section-button');
            const assignedContent = document.getElementById('assigned-content');
            const missingContent = document.getElementById('missing-content');

            buttons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default action of button click

                    // Remove active class from all buttons
                    buttons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to the clicked button
                    this.classList.add('active');

                    // Show/hide content based on section
                    const section = this.getAttribute('data-section');

                    if (section === 'assigned') {
                        assignedContent.style.display = 'block';
                        missingContent.style.display = 'none';
                    } else if (section === 'missing') {
                        assignedContent.style.display = 'none';
                        missingContent.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>
