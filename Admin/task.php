<?php
// Database connection
include 'connection.php';

// Handle AJAX request for grades
if (isset($_POST['department_id'])) {
    $dept_id = $_POST['department_id'];

    // SQL query to fetch content based on department ID
    $sql = "SELECT DISTINCT ContentID, Title, LEFT(Captions, 50) AS Captions FROM feedcontent WHERE dept_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $grades = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
    }

    echo json_encode($grades);
    exit; // Exit after handling AJAX request

}

// SQL query to fetch departments
$sql = "SELECT dept_ID, dept_name FROM department";
$result = $conn->query($sql);

// Prepare response
$departments = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Fetch reminders for display
$sql = "SELECT t.TaskID AS TaskID, t.Title AS TaskTitle, t.taskContent, t.DueDate, d.dept_name, fc.Title AS ContentTitle, fc.Captions
        FROM tasks t
        LEFT JOIN feedcontent fc on t.ContentID = fc.ContentID
        LEFT JOIN department d on fc.dept_ID = d.dept_ID
        WHERE t.Type = 'Task'";
$result = $conn->query($sql);

$tasks = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}


// Handle AJAX request for deleting a task
if (isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // SQL query to delete a task based on TaskID
    $sql = "DELETE FROM tasks WHERE TaskID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $task_id);

    if ($stmt->execute()) {
        $response = array('success' => true, 'message' => 'Task deleted successfully.');
    } else {
        $response = array('success' => false, 'message' => 'Failed to delete task.');
    }

    echo json_encode($response);
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: ;
            overflow: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            margin-top: -10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
        }

        .header h1 {
            color: #333;
            margin: 0;
            font-size: 1.5rem;
        }

        .buttonTask {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 1rem;
            padding: 10px;
        }

        .buttonTask:hover {
            background-color: #218838;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        /* Updated Styles for Form Layout */
        .form-group {
            margin-bottom: 1.5rem; /* Space below each form group */
        }

        label {
            display: block; /* Ensures label takes up full width */
            margin-bottom: 0.5rem; /* Space between label and input */
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%; /* Full width of the container */
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Includes padding and border in width calculation */
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: #007bff; /* Highlight border color on focus */
            outline: none; /* Remove default outline */
        }

        textarea {
            resize: vertical; /* Allows vertical resizing only */
        }


        .buttonSubmit {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 25px;
        }

        .buttonSubmit:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 118vw; /* Full viewport width */
            height: 100vh; /* Full viewport height */
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 100vw; /* 80% of viewport width */
            max-width: 1200px; /* Optional: maximum width */
            height: 90vh; /* 80% of viewport height */
            max-height: 800px; /* Optional: maximum height */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow-y: auto; /* Scroll if content exceeds the height */
            position: relative;
            top: 50%; /* Center the modal vertically */
            transform: translateY(-50%); /* Center the modal vertically */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            background-color: #fff;
        }

        table th {
            background-color: #9b2035;
            color: #fff;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }
        .buttonDelete {
            background-color: #d92b2b;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 1rem;
            padding: 10px;
        }

        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-left,
        .form-right {
            flex: 1;
            min-width: 300px;
        }

        /* Additional space for attachment field */
        .form-left .form-group input[type="file"] {
            padding: 20px;
            height: 150px; /* Increase height to make it more spacious */
            border: 2px dashed #ccc;
            background-color: #fafafa;
            display: block;
            margin-top: 10px;
        }
        
        .form-right label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-right select,
        .form-right input[type="date"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-right select:focus,
        .form-right input[type="date"]:focus {
            border-color: #007bff;
            outline: none;
        }

        /*---------------------Update Modal-----------*/
        input[type="text"],
        input[type="date"],
        textarea #update_instructions,
        select {
            width: 100%; /* Full width of the container */
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Includes padding and border in width calculation */
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: #007bff; /* Highlight border color on focus */
            outline: none; /* Remove default outline */
        }

        textarea#update_instructions{
            resize: vertical; /* Allows vertical resizing only */
            height: 160px; /* Adjust the height as needed */
        }

        .update-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 118vw; /* Full viewport width */
            height: 100vh; /* Full viewport height */
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .update-modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 70vw; /* 100% of viewport width */
            max-width: 1200px; /* Optional: maximum width */
            height: 70vh; /* 70% of viewport height */
            max-height: 800px; /* Optional: maximum height */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow-y: auto; /* Scroll if content exceeds the height */
            position: relative;
            top: 50%; /* Center the modal vertically */
            transform: translateY(-50%); /* Center the modal vertically */
        }

        .update-form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .update-form-left,
        .update-form-right {
            flex: 1;
            min-width: 300px;
        }
        
        .update-form-right label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .update-form-right select,
        .update-form-right input[type="date"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .update-form-right select:focus,
        .update-form-right input[type="date"]:focus {
            border-color: #007bff;
            outline: none;
        }
        /*!--------------------Update Modal---------------*/

        .buttonEdit{
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 1rem;
            padding: 10px;
        }

        .buttonEdit:hover {
            background-color: #218838;
        }
       
        .button-group {
            display: flex; /* Align buttons in a row */
            gap: 10px; /* Space between the buttons */
        }

        .buttonEdit,
        .buttonDelete {
            flex: 1; /* Make buttons take equal width if needed */
            text-align: center; /* Center text within buttons */
        }
        
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            background-color: #fff;
            max-width: 200px; /* Adjust width as needed */
            white-space: nowrap; /* Prevent text wrapping */
            overflow: hidden; /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis */
        }
        
        table td p {
            margin: 0; /* Remove default margins */
            line-height: 1.4; /* Improve readability */
        }

        .search-container {
            display: flex;
            align-items: center;
            position: relative;
        }

        .search-container .search-bar {
            display: none;
            width: 100%;
            max-width: 300px;
        }

        .search-bar input {
            width: 200px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-container .search-icon {
            cursor: pointer;
            font-size: 1.5em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <?php include 'navbar.php'; ?>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include 'topbar.php'; ?>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="header">
                <h1>Tasks</h1>
                <div class="button-group">
                    <button type="button" class="buttonTask" onclick="openModal()">Create Task</button>
                    <div class="search-container">
                        <i class="fas fa-search search-icon" onclick="toggleSearchBar()"></i>
                        <div class="search-bar">
                            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search for names..">
                        </div>
                    </div>
                </div>
            </div>


            <!-- Task Table -->
            <div class="container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="taskTableBody">
                    <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['TaskTitle']); ?></td>
                                <td><p><?php echo htmlspecialchars($task['taskContent']); ?></p></td>
                                <td><?php echo htmlspecialchars($task['dept_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['ContentTitle'] . ' - ' . $task['Captions']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($task['DueDate']))); ?></td>
                                <td>
                                    <div class="button-group">
                                        <button class="buttonEdit" 
                                            onclick="editTask(
                                                '<?php echo $task['TaskID']; ?>', 
                                                '<?php echo htmlspecialchars(addslashes($task['TaskTitle']), ENT_QUOTES); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($task['taskContent']), ENT_QUOTES); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($task['dept_name']), ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars(addslashes($task['ContentTitle'] . ' - ' . $task['Captions']), ENT_QUOTES); ?>',
                                                '<?php echo $task['DueDate']; ?>'
                                            )">Edit</button>
                                        <button class="buttonDelete" onclick="deleteTask('<?php echo $task['TaskID']; ?>')">Delete</button>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="taskModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <div class="container">
                        <h1>New Task</h1> <!-- Added header here -->
                        <form id="taskForm" action="/submit-task" method="post" enctype="multipart/form-data">
                            <div class="form-container">
                                <div class="form-left">
                                    <!-- Title and Instructions -->
                                    <div class="form-section">
                                        <label for="title">Title:</label>
                                        <div class="form-group">
                                            <input type="text" id="title" name="title" required>
                                        </div>
                                        <label for="instructions">Instructions:</label>
                                        <div class="form-group">
                                            <textarea id="instructions" name="instructions" rows="4" required></textarea>
                                        </div>
                                    </div>

                                    <!-- Attachment -->
                                    <div class="form-section">
                                        <label for="file">Attach Files:</label>
                                        <div class="form-group">
                                            <input type="file" id="file" name="file[]" multiple>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-right">
                                    <!-- Department, Grade, User, and Due Date -->
                                    <div class="form-section">
                                        <label for="department">Department:</label>
                                        <div class="form-group">
                                            <select id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept) : ?>
                                                    <option value="<?= $dept['dept_ID'] ?>"><?= $dept['dept_name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <label for="grade">Grade:</label>
                                        <div class="form-group">
                                            <select id="grade" name="grade" required>
                                                <option value="">Select Grade</option>
                                            </select>
                                        </div>
                                        <label for="due-date">Due Date:</label>
                                        <div class="form-group">
                                            <input type="date" id="due-date" name="due-date" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="buttonSubmit">Publish!</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Update Modal -->
            <div id="editModal" class="update-modal">
                <div class="update-modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <div class="container">
                        <h1>Edit Task</h1>
                        <form id="updateForm">
                            <input type="hidden" id="update_task_id" name="update_task_id">
                            <div class="update-form-container">
                                <div class="update-form-left">
                                    <!-- Title and Instructions -->
                                    <div class="form-section">
                                        <label for="update_title">Title:</label>
                                        <div class="form-group">
                                            <input type="text" id="update_title" name="update_title" required>
                                        </div>
                                        <label for="update_instructions">Instructions:</label>
                                        <div class="form-group">
                                            <textarea id="update_instructions" name="update_instructions" rows="4" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="update-form-right">
                                <div class="form-section">
                                        <label for="edit_department">Department:</label>
                                        <div class="form-group">
                                            <select id="edit_department" name="edit_department" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept) : ?>
                                                    <option value="<?= $dept['dept_ID'] ?>"><?= $dept['dept_name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <label for="update_grade">Grade:</label>
                                        <div class="form-group">
                                            <select id="update_grade" name="update_grade" required>
                                                <option value="">Select Grade</option>
                                            </select>
                                        </div>
                                        <!-- Due Date -->
                                        <div class="form-section">
                                            <label for="update_due_date">Due Date:</label>
                                            <div class="form-group">
                                                <input type="date" id="update_due_date" name="update_due_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="buttonSubmit" onclick="updateTask()">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script>
       document.getElementById('department').addEventListener('change', function() {
            var deptId = this.value;
            var gradeSelect = document.getElementById('grade');
            gradeSelect.innerHTML = '<option value="">Select Grade</option>'; // Reset the grades dropdown

            if (deptId) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true); // Send request to the same PHP file
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var grades = JSON.parse(xhr.responseText);
                            console.log('Grades:', grades); // Debugging output
                            grades.forEach(function(grade) {
                                var option = document.createElement('option');
                                option.value = grade.ContentID;
                                option.textContent = grade.Title + ' - ' + grade.Captions;
                                gradeSelect.appendChild(option);
                            });
                        } catch (e) {
                            console.error('Parsing error:', e);
                        }
                    } else {
                        console.error('Request failed. Status:', xhr.status);
                    }
                };
                xhr.send('department_id=' + encodeURIComponent(deptId));
            }
        });

        document.getElementById('taskForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data); // Log the response for debugging
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task created successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // Reload page to reflect changes
                    });
                    form.reset();
                    closeModal(); // Close modal after successful submission
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message // Display error message from PHP
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while creating the task.'
                });
            });
        });



        function openModal() {
            document.getElementById('taskModal').style.display = 'block';
        }


        function closeModal() {
            document.getElementById('taskModal').style.display = 'none';
        }


        function loadGrades(deptId, gradeSelectId, selectedGrade) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true); // Send request to the same PHP file
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var grades = JSON.parse(xhr.responseText);
                        var gradeSelect = document.getElementById('update_grade');
                        gradeSelect.innerHTML = '<option value="">Select Grade</option>'; // Reset the grades dropdown
                        
                        grades.forEach(function(grade) {
                            var option = document.createElement('option');
                            option.value = grade.ContentID; // Changed to ContentID
                            option.textContent = grade.Title + ' - ' + grade.Captions; // Include Title and Caption
                            if (grade.ContentID == selectedGrade) {
                                option.selected = true; // Select the option if it matches the selectedGrade
                            }
                            gradeSelect.appendChild(option);
                        });
                    } catch (e) {
                        console.error('Parsing error:', e);
                    }
                } else {
                    console.error('Request failed. Status:', xhr.status);
                }
            };
            xhr.send('department_id=' + encodeURIComponent(deptId));
        }

        document.getElementById('edit_department').addEventListener('change', function() {
            var deptId = this.value;
            loadGrades(deptId, 'grade', ''); // Reset grade selection
        });

        function editTask(taskID, title, content, deptName, gradeTitle, dueDate) {
            document.getElementById('update_task_id').value = taskID;
            document.getElementById('update_title').value = title;
            document.getElementById('update_instructions').value = content;
            document.getElementById('update_due_date').value = dueDate;

            var departmentSelect = document.getElementById('edit_department');
            var gradeSelect = document.getElementById('update_grade');

            // Set selected department
            Array.from(departmentSelect.options).forEach(option => {
                if (option.text === deptName) {
                    option.selected = true;
                    loadGrades(option.value, 'update_grade', gradeTitle);
                }
            });

            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function updateTask() {
            const form = document.getElementById('updateForm');
            const formData = new FormData(form);

            // Log form data for debugging
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            fetch('update_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Update Response:', data); // Log the response for debugging
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Task updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // Reload page to reflect changes
                    });
                    closeEditModal(); // Close modal after successful update
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the task.'
                });
            });
        }



        function deleteTask(taskId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX request to delete task
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Your task has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload(); // Reload page to reflect changes
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the task.',
                                'error'
                            );
                        }
                    };
                    xhr.send('task_id=' + encodeURIComponent(taskId));
                }
            })
        }

        // JavaScript for search functionality
        function toggleSearchBar() {
            var searchBar = document.querySelector('.search-bar');
            searchBar.style.display = searchBar.style.display === 'none' || searchBar.style.display === '' ? 'block' : 'none';
        }

        document.getElementById('searchInput').addEventListener('input', function() {
            var searchValue = this.value.trim().toLowerCase();
            var rows = document.querySelectorAll('#taskTableBody tr');

            rows.forEach(function(row) {
                var title = row.getElementsByTagName('td')[0]; // Assuming title is the first column
                if (title) {
                    var textValue = title.textContent || title.innerText;
                    if (textValue.toLowerCase().indexOf(searchValue) > -1) {
                        row.style.display = ''; // Show row if the search term matches
                    } else {
                        row.style.display = 'none'; // Hide row if no match
                    }
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>