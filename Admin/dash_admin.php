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

// Fetch data from the database
$totalUsersQuery = "SELECT COUNT(*) as total FROM useracc";
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
$totalUsers = mysqli_fetch_assoc($totalUsersResult)['total'];

$pendingUsersQuery = "SELECT COUNT(*) as total FROM useracc WHERE Status='pending'";
$pendingUsersResult = mysqli_query($conn, $pendingUsersQuery);
$pendingUsers = mysqli_fetch_assoc($pendingUsersResult)['total'];

$totalDocumentsQuery = "SELECT COUNT(*) as total FROM documents";
$totalDocumentsResult = mysqli_query($conn, $totalDocumentsQuery);
$totalDocuments = mysqli_fetch_assoc($totalDocumentsResult)['total'];

$totalDepartmentQuery = "SELECT COUNT(*) as total FROM department";
$totalDepartmentResult = mysqli_query($conn, $totalDepartmentQuery);
$totalDepartment = mysqli_fetch_assoc($totalDepartmentResult)['total'];

// Fetch today's document count
$todayDocumentsQuery = "SELECT COUNT(*) as total FROM documents WHERE DATE(timestamp) = CURDATE()";
$todayDocumentsResult = mysqli_query($conn, $todayDocumentsQuery);
$todayDocuments = mysqli_fetch_assoc($todayDocumentsResult)['total'];

// Fetch recent documents
function formatDate($timestamp) {
    $date = new DateTime($timestamp);
    $now = new DateTime();
    $yesterday = (new DateTime())->modify('-1 day');

    if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
        return 'Today ' . $date->format('g:i A'); // 12-hour format with AM/PM
    } elseif ($date->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        return 'Yesterday ' . $date->format('g:i A'); // 12-hour format with AM/PM
    } else {
        return $date->format('F j, Y g:i A'); // 12-hour format with AM/PM
    }
}

// Fetch recent documents
$recentDocumentsQuery = "SELECT * FROM documents ORDER BY timestamp DESC LIMIT 5";
$recentDocumentsResult = mysqli_query($conn, $recentDocumentsQuery);
$recentDocuments = [];
while ($row = mysqli_fetch_assoc($recentDocumentsResult)) {
    // Remove the file extension from the name
    $fileNameWithoutExtension = pathinfo($row['name'], PATHINFO_FILENAME);
    $row['name'] = $fileNameWithoutExtension;
    $row['formatted_timestamp'] = formatDate($row['timestamp']);

    // Determine the icon based on document type
    switch ($row['mimeType']) {
        case 'application/pdf':
            $row['icon'] = 'bx bx-file-pdf'; // Icon for PDF files
            break;
        case 'image/jpeg':
        case 'image/jpg':
            $row['icon'] = 'bx bx-image'; // Icon for JPG images
            break;
        case 'image/png':
            $row['icon'] = 'bx bx-image-alt'; // Icon for PNG images
            break;
        case 'application/msword':
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            $row['icon'] = 'bx bx-file-doc'; // Icon for DOCX files
            break;
        case 'application/vnd.ms-excel':
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            $row['icon'] = 'bx bx-file-excel'; // Icon for XLSX files
            break;
        case 'text/plain':
            $row['icon'] = 'bx bx-file'; // Icon for TXT files
            break;
        default:
            $row['icon'] = 'bx bx-file'; // Default icon for other file types
            break;
    }

    $recentDocuments[] = $row;
}

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
        .info-data {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;

            margin-bottom: -10px; /* Reduce the margin-bottom */
        }

        .card {
            background-color: #9B2035;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 200px; /* Minimum width for each card */
            box-sizing: border-box;
            height: 290%; /* Adjust height if needed */
            margin-top: -50px;
        }

        .card .head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card .head h2, .card .head p {
            margin: 0;
        }
        

        .card .head i.icon {
            font-size: 40px;
            color: #9B2035;
        }

        .recent-documents-card h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 25px;
        }

        .recent-documents-container {
            max-height: 400px; /* Set a maximum height for the scrollable area */
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: hidden; /* Hide horizontal scrolling */
            background-color:#9b2035;
        }

        .recent-document {
            padding: 20px; /* Increased padding for more space */
            margin-bottom: 10px; /* Increased margin for more space between items */
            
            border: 1px solid #ddd; /* Add border here */
            border-radius: 5px; /* Match border-radius for consistency */
            box-sizing: border-box; /* Include padding and border in element's total width and height */
            display: flex; /* Align items horizontally */
            align-items: center; /* Center items vertically */
            background-color: #f9f9f9; /* Optional: Add a background color */
            overflow: hidden; /* Hide any overflowed content */
        }

        .recent-document .icon {
            font-size: 30px; /* Adjust icon size */
            margin-right: 15px; /* Space between icon and text */
            color: #9B2035; /* Icon color */
        }

        .recent-document h3 {
            margin: 0;
            color: #333;
            white-space: nowrap; /* Prevent text from wrapping to a new line */
            overflow: hidden; /* Hide overflowed text */
            text-overflow: ellipsis; /* Add ellipsis (...) for overflowed text */
            max-width: 300px; /* Optional: Set a maximum width for the text */
            font-size: 20px;
        }

        .recent-document p {
            margin: 5px 0 0;
            color: #666;
        }
        .new-container {
            position: relative;
            height: 150px;
            border-radius: 10px;
            background: linear-gradient(to left, #9b2035, #d0495e );
            
        }

        .new-container::before {
            
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(to left, #9b2035, #d0495e );
            clip-path: path('M0 0 C50 150, 150 0, 200 100, 300 200, 350 0, 400 200, 500 100, 600 300, 700 200, 800 100, 900 0, 1000 200, 1100 100, 1200 0, 1300 200, 1400 100, 1500 300, 1600 200, 1700 100, 1800 0, 1900 200, 2000 100, 2100 0, 2200 200, 2300 100, 2400 300, 2500 200, 2600 100, 2700 0, 2800 200, 2900 100, 3000 0, 3100 200, 3200 100, 3300 300, 3400 200, 3500 100, 3600 0, 3700 200, 3800 100, 3900 0, 4000 200, 4100 100, 4200 300, 4300 200, 4400 100, 4500 0');
            animation: animate 4s linear infinite;
        }

        @keyframes animate {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        .new-container h2 {
            color: #fff;
            margin-bottom: 10px;
            margin-top: -10px;
            
        }

        .new-container p {
            color: #fff;
            font-size: 24px;
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-welcome {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .chart-container{
            flex-direction: column;
        }
        
        
        #chart {
            width: 300%; /* Adjust width as needed */
            max-width: 500px; /* Set a maximum width for the chart */
            height: 300px; /* Adjust height as needed */
            margin: auto; /* Center align the chart container */
        }

        .content-data {
            flex: 1; /* Allow items to grow and shrink */
            min-width: 592px; /* Set a minimum width for responsiveness */
            background: var(--light);
            border-radius: 10px;
            box-shadow: 4px 4px 16px rgba(0, 0, 0, .1);
            margin-top: 35px;
            margin-left: 10px;
            
        }

        .sales-report-container {
            width: 200%; /* Adjust width to fit the container */
            max-width: 800px; /* Set a maximum width if needed */
            height: 500px; /* Adjust height as needed */
            background: var(--light); /* Use a different background if needed */
            border-radius: 10px;
            box-shadow: 4px 4px 16px rgba(0, 0, 0, .1);
            margin-left: 18px;
        }


        .content-data .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .content-data .head h2 {
            font-size: 20px;
            font-weight: 600;
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
            <div class="info-data">
                <div class="row">
                    <div class="col-md-8" >
                        <div class="info-data">
                            <div class="card" style="background-color: ; height: 150px; position: relative; padding: 10px;border-radius: 10px;box-shadow: 4px 4px 16px rgba(0, 0, 0, .05);">
                                <div style="padding: 20px; " >
                                    <h1 style="font-weight:bold; color: #9b2035; margin-top:20px;">
                                            Hello, <?php echo htmlspecialchars($fname); ?> !
                                            <img src="img/EYYY.gif" alt="Animated GIF" style="height: 40px; vertical-align: middle;">
                                        </h1>										
                                </div>
                                    <img src="img/card.png" alt="description of image" style="position: absolute; right: 0; top: -63%; height: 320px; max-width: none;">
                            </div>
                        </div>
                    </div>
                    <div class ="col-md-4" style="margin-top: -13px; margin-bottom: 35px;">
                        <div class="new-container" style="text-align: center; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
                            <h2 style="margin: 0; font-size: 50px; color: #fff;"><h2><?php echo $todayDocuments; ?></h2>
                            <p>Documents Today</p></h5>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="info-data">
                            <div class="card">
                                <div class="head">
                                    <div>
                                        <h2 "><?php echo $totalUsers; ?></h2>
                                            <p style="font-weight:bold;">Total Users</p>
                                        </div>
                                    <i class='bx bx-user icon'></i>
                                </div>
                            </div>
                            <div class="card">
                                <div class="head">
                                    <div>
                                        <h2><?php echo $pendingUsers; ?></h2>
                                            <p style="font-weight:bold;">Pending Users</p>
                                        </div>
                                    <i class='bx bx-hourglass icon'></i>
                                </div>
                            </div>
                            <div class="card">
                                <div class="head">
                                    <div>
                                        <h2><?php echo $totalDocuments; ?></h2>
                                            <p style="font-weight:bold;">Total Documents</p>
                                    </div>
                                    <i class='bx bx-file icon'></i>
                                </div>
                            </div>
                            <div class="card">
                                <div class="head">
                                    <div>
                                        <h2><?php echo $totalDepartment; ?></h2>
                                            <p style="font-weight:bold;">Total Department</p>
                                    </div>
                                <i class='bx bx-task icon'></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Recent Documents Card -->
                <div class="data">
                <div class="content-data" style="background-color:#9b2035;">
                        <div class="head">
                            <h2 style="color:#fff;">Recent Documents</h2>
                        </div>
                        <div class="recent-documents-card">
                            <div class="recent-documents-container">
                                <?php foreach ($recentDocuments as $doc): ?>
                                    <div class="recent-document">
                                        <i class="<?php echo htmlspecialchars($doc['icon']); ?> icon"></i>
                                        <div>
                                            <h3><?php echo htmlspecialchars($doc['name']); ?></h3>
                                            <p><?php echo htmlspecialchars($doc['formatted_timestamp']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="content-data sales-report-container" sty>
                        <div class="head">
                            <h3>File Type Distribution</h3>
                        </div>
                        <div class="chart">
                            <div id="chart"></div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
	<script src="assets/js/script.js"></script>
    <script>

        // Fetch data from PHP
        var documentCounts = <?php echo json_encode(array_column($documentTrendsData, 'document_count')); ?>;
        var months = <?php echo json_encode(array_column($documentTrendsData, 'month')); ?>;

        // Create the chart
        var ctx = document.getElementById('documentTrendsCanvas').getContext('2d');
        var documentTrendsChart = new Chart(ctx, {
            type: 'line', // Type of chart
            data: {
                labels: months, // X-axis labels
                datasets: [{
                    label: 'Documents', // Label for the dataset
                    data: documentCounts, // Data points
                    borderColor: 'rgba(75, 192, 192, 1)', // Line color
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area color
                    borderWidth: 2, // Line width
                    fill: true // Whether to fill the area under the line
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Task Completion Chart
        var taskCompletionOptions = {
            series: [{
                name: 'Completed Tasks',
                data: <?php echo json_encode(array_column($taskCompletionData, 'completed_count')); ?>
            }, {
                name: 'Total Tasks',
                data: <?php echo json_encode(array_column($taskCompletionData, 'task_count')); ?>
            }, {
                name: 'Completion Rate (%)',
                data: <?php echo json_encode(array_column($taskCompletionData, 'completion_rate')); ?>
            }],
            chart: {
                type: 'line',
                height: 350
            },
            xaxis: {
                categories: <?php echo json_encode(array_column($taskCompletionData, 'month')); ?>
            }
        };
        var taskCompletionChart = new ApexCharts(document.querySelector("#task-completion-chart"), taskCompletionOptions);
        taskCompletionChart.render();


        // Prepare data for the file type donut chart
        var fileTypeData = {
            series: <?php echo json_encode($counts); ?>,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: <?php echo json_encode($fileTypes); ?>,
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%' // Adjust the size of the donut hole
                    }
                }
            }
        };
        var fileTypeChart = new ApexCharts(document.querySelector("#fileTypeChart"), fileTypeData);
        fileTypeChart.render();
    </script>
</body>
</html>
