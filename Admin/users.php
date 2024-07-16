    <?php
    include 'connection.php';

    // Handle user approval or rejection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE user_ID = ?");
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE user_ID = ?");
        }

        if (isset($stmt)) {
            $stmt->bind_param("i", $id);
            $response = array('status' => 'error');
            if ($stmt->execute()) {
                $response['status'] = 'success';
            }
            $stmt->close();
            echo json_encode($response);
            $conn->close();
            exit;
        }
    }

    // Fetch pending users
    $sql = "SELECT user_ID, first_name, middle_initial, last_name, role, date_registered FROM users WHERE status = 'pending'";
    $result = $conn->query($sql);

    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $conn->close();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Approval</title>
        <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Add FontAwesome CDN -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .container {
                display: grid;
                grid-template-columns: repeat(3, 1fr); /* 3 equal columns */
                gap: 20px; /* Gap between grid items */
                margin: 0 auto; /* Center the grid */
            }
            h1 {
                margin-bottom: 15px;
            }
            .user-card {
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .user-card h2 {
                margin: 0;
                font-size: 1.2em;
            }
            .user-card .details {
                flex: 1;
                margin-right: 20px;
            }
            .user-card .actions {
                display: flex;
                gap: 10px; /* Add space between icons */
            }
            .user-card .actions i {
                font-size: 1.5em; /* Adjust size of icons */
                cursor: pointer; /* Pointer cursor on hover */
                transition: color 0.3s; /* Smooth color change */
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px; /* Set fixed width for the circle */
                height: 40px; /* Set fixed height for the circle */
                border-radius: 50%; /* Make the border round */
                border: 2px solid; /* Border thickness */
            }
            .user-card .actions .fa-check {
                border-color: green; /* Green border for check icon */
                color: white; /* Green color for check icon */
                background-color: green;
            }
            .user-card .actions .fa-times {
                border-color: red; /* Red border for times icon */
                color: white; /* Red color for times icon */
                background-color: red;
            }
            .user-card .actions i:hover {
                color: #007bff; /* Change color on hover */
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
                <h1>Pending User Approvals</h1>
                <div class="container">
                    <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <div class="details">
                            <h2><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['middle_initial']) . ' ' . htmlspecialchars($user['last_name']); ?></h2>
                            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                            <p><strong>Registered on:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($user['date_registered']))); ?></p>
                        </div>
                        <div class="actions">
                            <i class="fas fa-check" onclick="approveUser(<?php echo $user['user_ID']; ?>)" title="Approve"></i>
                            <i class="fas fa-times" onclick="rejectUser(<?php echo $user['user_ID']; ?>)" title="Reject"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </section>

        <script>
            function approveUser(id) {
                modifyUserStatus(id, 'approve');
            }

            function rejectUser(id) {
                modifyUserStatus(id, 'reject');
            }

            function modifyUserStatus(id, action) {
                fetch('approve_user.php', { // Ensure this URL is correct
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data); // Debugging output
                    if (data.status === 'success') {
                        if (data.email_status === 'sent') {
                            alert(action === 'approve' ? 'User approved and notified via email.' : 'User rejected and notified via email.');
                        } else if (data.email_status === 'failed') {
                            alert('User status updated, but email notification failed. Error: ' + (data.email_error || 'Unknown error'));
                        } else {
                            alert('User status updated.');
                        }
                        location.reload(); // Reload to reflect changes
                    } else {
                        alert('An error occurred: ' + (data.error || 'Please try again.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }

        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="assets/js/script.js"></script>
    </body>
    </html>
