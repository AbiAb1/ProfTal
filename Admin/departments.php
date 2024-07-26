<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Add FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            max-width: 1200px; /* Increased width for better layout */
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        h1, h2 {
            color: #333;
            margin: 0;
        }

        .header h1 {
            margin-left: -10px;
        }

        .icon-button {
            background-color: #9b2035;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 90px;
            transition: background-color 0.3s;
            font-size: 24px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height:60px;
            width: 60px;

        }

        .icon-button:hover {
            background-color: #861c2e;
        }

        .icon-button i {
            margin: 0; /* Remove any default margins */
        }

        .departments-container {
            margin: 20px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Default to 3 columns */
            gap: 20px; /* Space between grid items */
        }

        .department {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box; /* Ensures padding and border are included in the element's total width and height */
        }

        .department h3 {
            margin: 0 0 10px;
        }

        .department p {
            margin: 0 0 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px; /* Adjust font size */
        }

        button:hover {
            background-color: #218838;
        }

        .btn-small {
            font-size: 12px; /* Smaller font size for the delete button */
            padding: 5px 10px; /* Smaller padding */
            background-color: #dc3545; /* Red color for delete button */
            transition: background-color 0.3s;
        }

        .btn-small:hover {
            background-color: #c82333; /* Darker red color on hover */
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #333;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        form button {
            background-color: #28a745;
            border-radius: 5px;
        }

        form button:hover {
            background-color: #218838;
        }

        /* Style for arrow icon link */
        .arrow-link {
            position: absolute;
            bottom: 10px; /* Adjust as needed */
            right: 10px; /* Adjust as needed */
            color: black; /* Change the arrow color to red */
            font-size: 24px; /* Size of the arrow icon */
            text-decoration: none;
            transition: color 0.3s, border 0.3s; /* Transition for both color and border */
        }

        .arrow-link:hover {
            border-radius: 55%; /* Round border */
            background-color: gray;
            
        }

        .department {
            position: relative; /* Ensures the arrow icon is positioned relative to the department container */
        }


        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .departments-container {
                grid-template-columns: repeat(2, 1fr); /* 2 columns for smaller screens */
            }
        }

        @media (max-width: 800px) {
            .departments-container {
                grid-template-columns: 1fr; /* 1 column for very small screens */
            }
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
            <div class="header">
                <h1>Department Management</h1>
                <button id="openModalBtn" class="icon-button">
                    <i class='bx bx-plus'></i>
                </button>
            </div>
            <div class="container">
                <div class="departments-container" id="departmentsContainer">
                    <!-- Departments will be loaded here -->
                </div>
            </div>
        </main>
    
        <!-- Create Department Modal -->
        <div id="createModal" class="modal">
            <div class="modal-content">
                <span class="close" id="createModalClose">&times;</span>
                <h2>Create Department</h2>
                <form id="createDepartmentForm">
                    <label for="departmentName">Department Name:</label><br>
                    <input type="text" id="departmentName" name="departmentName" required><br>
                    <label for="departmentInfo">Department Info:</label><br>
                    <textarea id="departmentInfo" name="departmentInfo" required></textarea><br>
                    <button type="submit">Create</button>
                </form>
            </div>
        </div>

        <!-- Update Department Modal -->
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <span class="close" id="updateModalClose">&times;</span>
                <h2>Update Department</h2>
                <form id="updateDepartmentForm">
                    <input type="hidden" id="updateDeptID" name="deptID">
                    <label for="updateDepartmentName">Department Name:</label><br>
                    <input type="text" id="updateDepartmentName" name="departmentName" required><br>
                    <label for="updateDepartmentInfo">Department Info:</label><br>
                    <textarea id="updateDepartmentInfo" name="departmentInfo" required></textarea><br>
                    <button type="submit">Update</button>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadDepartments();

            // Create Department
            document.getElementById('createDepartmentForm').addEventListener('submit', function (event) {
                event.preventDefault();
                createDepartment();
            });

            // Update Department
            document.getElementById('updateDepartmentForm').addEventListener('submit', function (event) {
                event.preventDefault();
                updateDepartment();
            });

            // Modal functionality
            var createModal = document.getElementById("createModal");
            var updateModal = document.getElementById("updateModal");
            var createBtn = document.getElementById("openModalBtn");
            var createClose = document.getElementById("createModalClose");
            var updateClose = document.getElementById("updateModalClose");

            createBtn.onclick = function() {
                createModal.style.display = "flex";
            }

            createClose.onclick = function() {
                createModal.style.display = "none";
            }

            updateClose.onclick = function() {
                updateModal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == createModal) {
                    createModal.style.display = "none";
                }
                if (event.target == updateModal) {
                    updateModal.style.display = "none";
                }
            }
        });

        function loadDepartments() {
            fetch('fetch_dept.php?action=read')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('departmentsContainer');
                    container.innerHTML = '';
                    if (data.department && data.department.length) {
                        data.department.forEach(department => {
                            const div = document.createElement('div');
                            div.classList.add('department');
                            div.innerHTML = `
                                <h3>${department.dept_name}</h3>
                                <p>${department.dept_info}</p>
                                <div class="button-group">
                                    <button onclick="showUpdateModal(${department.dept_ID}, '${department.dept_name}', '${department.dept_info}')">Update</button>
                                    <button class="btn-small" onclick="deleteDepartment(${department.dept_ID})">Delete</button>
                                    <a href="grades.php?deptID=${department.dept_ID}&deptName=${department.dept_name}" class="arrow-link">
                                        <i class='bx bx-right-arrow-alt'></i>
                                    </a>
                                </div>
                            `;
                            container.appendChild(div);
                        });
                    } else {
                        container.innerHTML = '<p>No departments found.</p>';
                    }
                });
        }

        function createDepartment() {
            const formData = new FormData(document.getElementById('createDepartmentForm'));
            fetch('department_management.php?action=create', {
                method: 'POST',
                body: JSON.stringify({
                    name: formData.get('departmentName'),
                    info: formData.get('departmentInfo')
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Hide the modal
                    document.getElementById('createModal').style.display = 'none';
                    // Reset the form
                    document.getElementById('createDepartmentForm').reset();
                    // Reload the departments to show the new department
                    loadDepartments();
                } 
                else {
                    alert('Error creating department: ' + (data.message || 'Unknown error'));
                }
            });
        }

        function deleteDepartment(id) {
            fetch(`delete_department.php?action=delete&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadDepartments();
                } else {
                    alert('Error deleting department');
                }
            });
        }

        function showUpdateModal(id, name, info) {
            document.getElementById('updateDeptID').value = id;
            document.getElementById('updateDepartmentName').value = name;
            document.getElementById('updateDepartmentInfo').value = info;
            document.getElementById('updateModal').style.display = 'flex';
        }

        function updateDepartment() {
            const formData = new FormData(document.getElementById('updateDepartmentForm'));
            fetch('update_department.php?action=update', {
                method: 'POST',
                body: JSON.stringify({
                    id: formData.get('deptID'),
                    name: formData.get('departmentName'),
                    info: formData.get('departmentInfo')
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadDepartments();
                    document.getElementById('updateDepartmentForm').reset();
                    document.getElementById('updateModal').style.display = 'none';
                } else {
                    alert('Error updating department: ' + (data.message || 'Unknown error'));
                }
            });
        }
    </script>



    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>