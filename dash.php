<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$loginSuccess = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : false;
if ($loginSuccess) {
    unset($_SESSION['login_success']); // Unset the session variable after use
}

include 'connection.php';

$user_id = $_SESSION['user_id'];

// Fetch user information
$sql_user = "SELECT * FROM useracc WHERE UserID = ?";
if ($stmt_user = $conn->prepare($sql_user)) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    $fname = '';
    if ($result_user->num_rows > 0) {
        $row = $result_user->fetch_assoc();
        $fname = $row['fname'];
    }
    $stmt_user->close();
} else {
    echo "Error preparing user query";
}

// Fetch tasks with DueDate and Feed Content Title
$sql_tasks = "SELECT ts.Title, ts.DueDate, ts.ContentID, ts.taskContent,ts.Type, fc.Title as feedContentTitle
              FROM tasks ts
              INNER JOIN usercontent uc ON ts.ContentID = uc.ContentID
              INNER JOIN feedcontent fc ON ts.ContentID = fc.ContentID
              WHERE uc.UserID = ? AND uc.Status = 1";
if ($stmt_tasks = $conn->prepare($sql_tasks)) {
    $stmt_tasks->bind_param("i", $user_id);
    $stmt_tasks->execute();
    $result_tasks = $stmt_tasks->get_result();

    $events = [];
    while ($row = $result_tasks->fetch_assoc()) {
        $events[] = [
            'type' => $row['Type'],
            'title' => $row['Title'], // Concatenate Type and Title
            'start' => $row['DueDate'],
            'content' => $row['taskContent'], // Add content field
            'feedContentTitle' => "{$row['Type']}: {$row['feedContentTitle']}" // Add feed content title field
        ];
    }
    $stmt_tasks->close();
} else {
    echo "Error preparing tasks query";
}
// Fetch content count
$sql_feedcontent_count = "SELECT COUNT(fs.ContentID) AS contentCount
                          FROM feedcontent fs
                          INNER JOIN usercontent uc ON fs.ContentID = uc.ContentID
                          WHERE uc.UserID = ? AND uc.Status = 1";
if ($stmt_feedcontent_count = $conn->prepare($sql_feedcontent_count)) {
    $stmt_feedcontent_count->bind_param("i", $user_id);
    $stmt_feedcontent_count->execute();
    $result_feedcontent_count = $stmt_feedcontent_count->get_result();

    $contentCount = 0;
    if ($row = $result_feedcontent_count->fetch_assoc()) {
        $contentCount = $row['contentCount'];
    }
    $stmt_feedcontent_count->close();
} else {
    echo "Error preparing feedcontent count query";
}
// Fetch assigned tasks count
$sql_assigned_count = "SELECT COUNT(ts.TaskID) AS assignedCount
                       FROM tasks ts
                       INNER JOIN usercontent uc ON ts.ContentID = uc.ContentID
                       WHERE uc.UserID = ? AND uc.Status = 1 AND ts.Type = 'Task' AND ts.Duedate >= CURDATE()";
if ($stmt_assigned_count = $conn->prepare($sql_assigned_count)) {
    $stmt_assigned_count->bind_param("i", $user_id);
    $stmt_assigned_count->execute();
    $result_assigned_count = $stmt_assigned_count->get_result();

    $assignedCount = 0;
    if ($row = $result_assigned_count->fetch_assoc()) {
        $assignedCount = $row['assignedCount'];
    }
    $stmt_assigned_count->close();
} else {
    echo "Error preparing assigned tasks count query";
}

// Fetch missing tasks count
$sql_missing_count = "SELECT COUNT(ts.TaskID) AS missingCount
                      FROM tasks ts
                      INNER JOIN usercontent uc ON ts.ContentID = uc.ContentID
                      WHERE uc.UserID = ? AND uc.Status = 1 AND ts.Type = 'Task' AND ts.Duedate < CURDATE()";
if ($stmt_missing_count = $conn->prepare($sql_missing_count)) {
    $stmt_missing_count->bind_param("i", $user_id);
    $stmt_missing_count->execute();
    $result_missing_count = $stmt_missing_count->get_result();

    $missingCount = 0;
    if ($row = $result_missing_count->fetch_assoc()) {
        $missingCount = $row['missingCount'];
    }
    $stmt_missing_count->close();
} else {
    echo "Error preparing missing tasks count query";
}
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <title>Dashboard | Home</title>
    <style>
        .fc-button {
            background-color: #9b2035;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 14px;
        }
        .fc-button:hover {
            background-color: #a8243b;
        }
        .fc-icon {
            color: white;
        }
        .fc-day-number {
            color: black; /* Make the dates black */
        }
        .fc-unthemed .fc-today {
            background-color: #9b2035 !important; /* Color the today date */
            color: white !important; /* Make the text color white */
        }
        .fc-unthemed td.fc-today {
            background-color: transparent !important;
            color: white !important;
        }
        .fc-content-skeleton table,
        .fc-basic-view .fc-body .fc-row,
        .fc-row .fc-content-skeleton,
        .fc-row .fc-bg,
        .fc-day,
        .fc-day-top,
        .fc-bg table,
        .fc-bgevent-skeleton,
        .fc-content-skeleton td,
        .fc-axis {
            border: none !important; /* Remove the grid lines */
        }
        .fc-event-dot {
            border-radius: 50%;
            background-color: #9b2035; /* Dot color */
            height: 10px;
            width: 10px;
        }


        .modal-backdrop.show {
    background-color: rgba(0, 0, 0, 0.5); /* Slightly darker background */
}

/* Modal Container */
.modal-dialog {
    max-width: 600px; /* Adjust as needed */
    margin: 1.75rem auto;
}

/* Modal Content */
.modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

/* Modal Header */
.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #f7f7f7; /* Light grey background */
    padding: 1rem 1.5rem;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

/* Modal Title */
.modal-title {
    font-size: 1.25rem;
    font-weight: 500;
    color: #333;
}

/* Close Button */
.modal-header .close {
    font-size: 1.5rem;
    color: #333;
}

/* Modal Body */
.modal-body {
    padding: 1.5rem;
    color: #555; /* Darker grey text */
}

/* Modal Footer */
.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    background-color: #f7f7f7; /* Light grey background */
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Button Styling */
.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

/* Adjust Close Button Alignment */
.modal-footer .btn-secondary {
    margin-left: auto;
}

/* Optional: Adjust modal responsiveness */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
        max-width: 100%;
    }
}

/* Icon styles */
.modal-body i {
    color: #9b2035;
    margin-right: 10px;
}
/* Icon styles */
.modal-body i {
    color: #9b2035;
    margin-right: 10px;
    font-size: 24px; /* Increase font size */
}
.count {
            position: absolute;
            top: 40px;
            right: 40px;
            background-color: #fff;
            color: #9b2035;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size:60px;
        }
        .todo-count {
            position: absolute;
            top: 40px;
            right: 40px;
           
            color: #fff;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size:60px;
        }

.todo-text {
    position: absolute;
    bottom: 0;
    right: 0;
    margin: 10px; /* Adjust as needed */
    color: white; /* Adjust text color as needed */
}
.card{
    height:180px;
}
.new-container {
    position: relative;
    height: 320px;
    padding: 20px;
    border-radius: 10px;
    background: linear-gradient(to left, #9b2035, #d0495e );
    overflow: hidden;
}

.new-container::before {
    
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to left, #9b2035, #d0495e );
    clip-path: path('M0 0 C50 150, 150 0, 200 100, 250 0, 300 100, 350 0, 400 100, 450 0, 500 100, 550 0, 600 100, 650 0, 700 100, 750 0, 800 100, 850 0, 900 100, 950 0, 1000 100, 1050 0, 1100 100, 1150 0, 1200 100, 1250 0, 1300 100, 1350 0, 1400 100, 1450 0, 1500 100, 1550 0, 1600 100, 1650 0, 1700 100, 1750 0, 1800 100, 1850 0, 1900 100, 1950 0, 2000 100, 2050 0, 2100 100, 2150 0, 2200 100, 2250 0, 2300 100, 2350 0, 2400 100, 2450 0, 2500 100, 2550 0, 2600 100, 2650 0, 2700 100, 2750 0, 2800 100, 2850 0, 2900 100, 2950 0, 3000 100');
    border-radius: 10px;
    box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.1);
}

canvas {
    width: 100% !important;
    height: auto !important;
}
.task-item {
    position: relative; /* Required for positioning the button */
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect on all sides */
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin:10px;
}
.task-item h4 {
    margin: 0;
    font-size: 20px;
}

.task-item p {
    margin: 5px 0;
    font-size: 14px;
}

.complete-button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    background-color: #28a745; /* Green background */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.complete-button i {
    color: #fff;
    font-size: 14px;
}

    </style>
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
            <h1 class="title">Dashboard</h1>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-data">
                                <div class="card" style="background-color: ; height: 150px; position: relative; padding: 10px;border-radius: 10px;box-shadow: 4px 4px 16px rgba(0, 0, 0, .05);">
                                    <div style="padding: 20px; " >
                                    <h1 style="font-weight:bold; color: #9b2035;margin-bottom:-5px;">
                                        Hello, <?php echo htmlspecialchars($fname); ?> !
                                        <img src="img/icons/EYYY.gif" alt="Animated GIF" style="height: 40px; vertical-align: middle;">
                                    </h1>										
                                    <p style="color: grey; font-size:16px; ">You currently have <em style ="font-weight:bold; font-size:20px;"><?php echo htmlspecialchars($assignedCount); ?></em> tasks to accomplish. Finish it before the <br>assigned due! <em style ="font-weight:bold;  color:#9b2035">Have a nice day!</em></p>
                                    </div>
                                    <img src="img/card.png" alt="description of image" style="position: absolute; right: 0; top: -63%; height: 320px; max-width: none;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-data">
                                <div class="card" style="background-color:#9b2035;">
                                    <div class="head">
                                        <div>
                                            <img src="img/icons/todo.png" alt="To-Do Icon">
                                        </div>
                                    </div>
                                    <p class="todo-count"><?php echo htmlspecialchars($assignedCount); ?></p>
                                    <h6 class="todo-text">Assigned</h6>
                                </div>

                                <div class="card" style="background-color:#9b2035 ;">
                                    <div class="head">
                                        <div>
                                            <img src="img/icons/missing.png">
                                        </div>
                                    </div>
                                    <p class="todo-count"><?php echo htmlspecialchars($missingCount); ?></p>
                                    <h6 class="todo-text">Missing</h6>
                                </div>
                                <div class="card">
                                    <div class="head">
                                        <div>
                                            <img src="img/icons/list.png">
                                        </div>
                                    </div>
                                    <span class="count"><?php echo htmlspecialchars($contentCount); ?></span>
                                    <h6 class="todo-text" style="color:#9b2035">Subjects</h6>
                                </div>
                            </div>
                            <div class="data">
                                <div class="content-data" style="height: 455px;">
                                    <div class="row h-100">
                                        <!-- Left part for illustration -->
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="assets/images/todo2.png" alt="Illustration" style="width: 100%; height: auto;">
                                        </div>
                                        <!-- Right part for tasks -->
                                        <div class="col-md-6 d-flex flex-column" style="height: 400px;">
                                            <div class="head mb-3">
                                                <h3>Reminders</h3>
                                                <button type="button" class="btn btn-custom" data-toggle="modal" data-target="#addTaskModal" style="background-color:#9b2035; color:#fff;">
                                                    Add a Reminder
                                                </button>
                                            </div>
                                            <div id="tasksList" style="height: calc(100% - 60px); overflow-y: auto;">
                                                <!-- Tasks will be dynamically inserted here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>




                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-data">
                        <div class ="row">
                            <div class="col-md-12">
                                <div class="card" style =" margin-bottom:20px;height:490px; background-color: ; padding: 20px;border-radius: 10px;box-shadow: 4px 4px 16px rgba(0, 0, 0, .05);">
                                    <div id="calendar" style ="height:190px;"></div>
                                </div>
                            </div>
                            <div class ="col-md-12">
                            <div class="new-container" style="text-align: center; padding: 90px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
                                <h5 style="margin: 0; font-size: 90px; color: #fff;">20%</h5>
                                <p style="margin: 10px 0 0; font-size: 1rem; color: #fff;">We are 20% through the school year.</p>
                            </div>


                            </div>
                        </div>

                                
                    </div>
                </div>
            </div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:transparent;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><i class='bx bx-news'></i> <strong><span id="eventFeedContentTitle" style="font-weight:bold;font-size:50px;"></span></strong></p>
                <p><i class='bx bx-bookmark'></i> <strong><span id="eventTitle"></span></strong></p>
                <p><i class='bx bx-calendar'></i> <span id="eventDueDate"></span></p>
                <p><i class='bx bx-list-ul'></i> <span id="eventContent"></span></p>
                
            </div>
            
        </div>
    </div>
</div>

<!-- Add New Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:transparent;">
                <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addTaskForm">
                    <div class="form-group">
                        <label for="taskName">Task Name</label>
                        <input type="text" class="form-control" id="taskName" name="taskName" required>
                    </div>
                    <div class="form-group">
                        <label for="taskDate">Due Date</label>
                        <input type="date" class="form-control" id="taskDate" name="taskDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTaskBtn">Save Task</button>
            </div>
        </div>
    </div>
</div>




        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.41/moment-timezone-with-data.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar-timegrid.min.js"></script>


<script>
$(document).ready(function() {
    $('#saveTaskBtn').on('click', function() {
        const taskName = $('#taskName').val();
        const taskDate = $('#taskDate').val();

        if (taskName && taskDate) {
            $.ajax({
                url: 'addTask.php',
                type: 'POST',
                data: {
                    taskName: taskName,
                    taskDate: taskDate
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Added',
                        text: 'Your task has been added successfully!',
                    }).then(() => {
                        $('#addTaskModal').modal('hide');
                        // Optionally, refresh the calendar or update the task list here
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'An error occurred while adding the task.',
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Data',
                text: 'Please fill out both fields.',
            });
        }
    });
});
$(document).ready(function() {
    // Fetch tasks when the page loads
    $.ajax({
        url: 'fetch_todo.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (Array.isArray(response)) {
                let tasksHtml = '';

                response.forEach(task => {
                    tasksHtml += `
                        <div class="task-item">
                            <div class="task-details">
                                <h4>${task.Title}</h4>
                                <p>Due: ${task.Due}</p>
                                <p>Status: ${task.Status === '1' ? 'Pending' : 'Completed'}</p>
                            </div>
                            <div class="complete-button">
                                <i class="bx bxs-check-square"></i>
                            </div>
                        </div>
                    `;
                });

                $('#tasksList').html(tasksHtml);
            } else {
                $('#tasksList').html('<p>No tasks found.</p>');
            }
        },
        error: function(xhr, status, error) {
            $('#tasksList').html('<p>An error occurred while fetching tasks.</p>');
        }
    });
});

</script>



<script src="progress.js">
document.addEventListener('DOMContentLoaded', function() {
    const startDate = new Date('2024-07-15');
    const endDate = new Date('2025-03-09');
    const today = new Date();

    const totalDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
    const completedDays = (today - startDate) / (1000 * 60 * 60 * 24);
    const percentageCompleted = (completedDays / totalDays) * 100;

    const ctx = document.getElementById('progressChart').getContext('2d');
    const data = {
        datasets: [{
            data: [percentageCompleted, 100 - percentageCompleted],
            backgroundColor: ['#9b2035', '#d0495e'],
            borderWidth: 0
        }]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            tooltip: {
                enabled: false
            },
            legend: {
                display: false
            },
            beforeDraw: (chart) => {
                const { width, height, ctx } = chart;
                ctx.restore();
                const fontSize = (height / 160).toFixed(2);
                ctx.font = `${fontSize}em sans-serif`;
                ctx.textBaseline = "middle";
                const text = `${Math.round(percentageCompleted)}%`;
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: options
    });
});


</script>

    <script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'title',
            center: '',
            right: 'prev,next',
        },
        footer: {
            right: 'today month agendaWeek agendaDay'
        },
        defaultDate: moment().format('YYYY-MM-DD'),
        navLinks: true,
        editable: true,
        eventLimit: true,
        height: 450,
        timeZone: 'Asia/Manila', // Set timezone to Philippine Time
        events: <?php echo json_encode($events); ?>,
        dayRender: function(date, cell) {
            if (date.isSame(moment().tz('Asia/Manila'), 'day')) {
                cell.css("background-color", "rgba(155, 32, 53, 0.2)");
                cell.css("color", "#9b2035");
            }
        },
        eventClick: function(event, jsEvent, view) {
            $('#eventTitle').text(event.title);
            $('#eventType').text(event.type);
            $('#eventDueDate').text(moment(event.start).tz('Asia/Manila').format('MMMM Do YYYY'));
            $('#eventContent').text(event.content || 'No details available.');
            $('#eventFeedContentTitle').text(event.feedContentTitle || 'No feed content title available.');
            $('#eventModal').modal('show');
        }
    });
});

</script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('logout').addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will be logged out of your account!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, log me out!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });
    </script>
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($loginSuccess === true): ?>
        Swal.fire({
            title: 'Login Successful!',
            text: 'Welcome back, <?php echo htmlspecialchars($fname); ?>!',
            icon: 'success',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
