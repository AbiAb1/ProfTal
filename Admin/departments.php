<?php
include 'connection.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'];
        $info = isset($data['info']) ? $data['info'] : '';

        $stmt = $conn->prepare("INSERT INTO department (dept_name, dept_info) VALUES (?,?)");
        $stmt->bind_param("ss", $name, $info);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'error' => $stmt->error]);
        }

        $stmt->close();
        exit;

    case 'read':
        $result = $conn->query("SELECT * FROM department");
        $departments = [];

        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }

        echo json_encode(['departments' => $departments]);
        exit;

    case 'delete':
        $id = intval($_GET['id']);

        $stmt = $conn->prepare("DELETE FROM department WHERE dept_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'error' => $stmt->error]);
        }

        $stmt->close();
        exit;

    default:
        // No default action to avoid interfering with HTML rendering
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2 {
            text-align: center;
        }

        .form-container, .departments-container {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
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
                <div class="container">
                    <h1>Department Management</h1>
                    <div class="form-container">
                        <h2>Create Department</h2>
                        <form id="createDepartmentForm">
                            <label for="departmentName">Department Name:</label>
                            <input type="text" id="departmentName" name="departmentName" required>
                            <label for="departmentInfo">Department Info:</label>
                            <textarea id="departmentInfo" name="departmentInfo" required></textarea>
                            <button type="submit">Create</button>
                        </form>
                    </div>
                    <div class="departments-container">
                        <h2>Departments</h2>
                        <table id="departmentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Info</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Departments will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadDepartments();

            document.getElementById('createDepartmentForm').addEventListener('submit', function (event) {
                event.preventDefault();
                createDepartment();
            });
        });

        function loadDepartments() {
            fetch('department_management.php?action=read')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#departmentsTable tbody');
                    tbody.innerHTML = '';
                    data.departments.forEach(department => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${department.dept_ID}</td>
                            <td>${department.dept_name}</td>
                            <td>${department.dept_info}</td>
                            <td>
                                <button onclick="deleteDepartment(${department.dept_ID})">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                });
        }

        function createDepartment() {
            const name = document.getElementById('departmentName').value;
            const info = document.getElementById('departmentInfo').value;
            fetch('department_management.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name, info }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadDepartments();
                    document.getElementById('createDepartmentForm').reset();
                } else {
                    alert('Error creating department');
                }
            });
        }

        function deleteDepartment(id) {
            fetch(`department_management.php?action=delete&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadDepartments();
                } else {
                    alert('Error deleting department');
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
