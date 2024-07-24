<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
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

        h1 {
            color: #333;
            margin: 0;
        }

        .icon-button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 24px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-button:hover {
            background-color: #218838;
        }

        .arrow-link {
            font-size: 24px;
            color: black;
            text-decoration: none;
        }

        .arrow-link:hover {
            border: 2px solid gray;
            border-radius: 50%;
        }

        .grades-container {
            margin: 20px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .title {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .title h3 {
            margin: 0 0 10px;
        }

        .title p {
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
            font-size: 14px;
        }

        button:hover {
            background-color: #218838;
        }

        .btn-small {
            font-size: 12px;
            padding: 5px 10px;
            background-color: #dc3545;
            transition: background-color 0.3s;
        }

        .btn-small:hover {
            background-color: #c82333;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

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

        .title {
            position: relative; /* Ensures the arrow icon is positioned relative to the grade container */
        }

        @media (max-width: 1200px) {
            .grades-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 800px) {
            .grades-container {
                grid-template-columns: 1fr;
            }
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
            <div class="header">
                <h1>Grade Management</h1>
                <button id="openModalBtn" class="icon-button">
                    <i class='bx bx-plus'></i>
                </button>
            </div>
            <div class="container">
                <div class="grades-container" id="gradesContainer">
                    <!-- Grades will be loaded here -->
                    
                </div>
            </div>
        </main>
    
        <!-- Create Grade Modal -->
        <div id="createModal" class="modal">
            <div class="modal-content">
                <span class="close" id="createModalClose">&times;</span>
                <h2>Create Grade</h2>
                <form id="createGradeForm">
                    <input type="hidden" id="deptID" name="deptID" value="<?php echo htmlspecialchars($_GET['deptID']); ?>">
                    <label for="title">Grade:</label><br>
                    <input type="text" id="title" name="title" required><br>
                    <label for="caption">Section:</label><br>
                    <input type="text" id="caption" name="caption" required><br>
                    <button type="submit">Create</button>
                </form>
            </div>
        </div>

        <!-- Edit Grade Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" id="editModalClose">&times;</span>
                <h2>Edit Grade</h2>
                <form id="editGradeForm">
                    <input type="hidden" id="editContentID" name="contentID">
                    <input type="hidden" id="deptID" name="deptID" value="<?php echo htmlspecialchars($_GET['deptID']); ?>">
                    <label for="editTitle">Grade:</label><br>
                    <input type="text" id="editTitle" name="title" required><br>
                    <label for="editCaption">Section:</label><br>
                    <input type="text" id="editCaption" name="caption" required><br>
                    <button type="submit">Update</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deptID = new URLSearchParams(window.location.search).get('deptID');
            if (deptID) {
                loadGrades(deptID);
                loadDepartmentName(deptID); // Load department name
            }

            document.getElementById('createGradeForm').addEventListener('submit', function (event) {
                event.preventDefault();
                createGrade();
            });

            document.getElementById('editGradeForm').addEventListener('submit', function (event) {
                event.preventDefault();
                updateGrade();
            });

            document.getElementById('openModalBtn').addEventListener('click', function () {
                document.getElementById('createModal').style.display = 'flex';
            });

            document.getElementById('createModalClose').addEventListener('click', function () {
                document.getElementById('createModal').style.display = 'none';
            });

            document.getElementById('editModalClose').addEventListener('click', function () {
                document.getElementById('editModal').style.display = 'none';
            });
        });

        function loadGrades(deptID) {
            fetch(`grades_management.php?action=read&deptID=${deptID}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('gradesContainer');
                    container.innerHTML = '';
                    if (data.feedcontent && data.feedcontent.length) {
                        data.feedcontent.forEach(title => {
                            const div = document.createElement('div');
                            div.classList.add('title');
                            div.innerHTML = `
                                <h3>${title.Title}</h3>
                                <p>Section: ${title.Captions}</p>   
                                <div class="button-group">
                                    <button onclick="editGrade(${title.ContentD}, '${title.Title}', '${title.Captions}')">Edit</button>
                                    <button class="btn-small" onclick="deleteGrade(${title.ContentID})">Delete</button>
                                    <a href="content.php?ContentID=${title.ContentID}&Title=${title.Title}&Captions=${title.Captions}" class="arrow-link">
                                        <i class='bx bx-right-arrow-alt'></i>
                                    </a>
                                </div>
                            `;
                            container.appendChild(div);

                            // Load department name for this grade
                            loadDepartmentName(title.deptID, title.ContentID);
                        });
                    } else {
                        container.innerHTML = '<p>No grades found.</p>';
                    }
                });
        }

        function loadDepartmentName(deptID, ContentID) {
            fetch(`department_management.php?action=getName&deptID=${deptID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById(`deptName-${ContentID}`).textContent = data.departmentName; // Update department name
                    } else {
                        console.error('Failed to fetch department name');
                    }
                })
                .catch(error => console.error('Error fetching department name:', error));
        }

        function createGrade() {
            const deptID = document.getElementById('deptID').value;
            const title = document.getElementById('title').value;
            const caption = document.getElementById('caption').value;

            fetch('grades_management.php?action=create', {
                method: 'POST',
                body: JSON.stringify({ title, caption, deptID }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadGrades(deptID);
                    document.getElementById('createGradeForm').reset();
                    document.getElementById('createModal').style.display = 'none';
                } else {
                    alert('Error creating grade: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function editGrade(ContentID, titleValue, captionValue) {
            const editModal = document.getElementById('editModal');
            const editTitleInput = document.getElementById('editTitle');
            const editCaptionInput = document.getElementById('editCaption');
            const editContentIDInput = document.getElementById('editContentID');

            editTitleInput.value = titleValue;
            editCaptionInput.value = captionValue;
            editContentIDInput.value = ContentID;

            editModal.style.display = 'flex';

            document.getElementById('editGradeForm').addEventListener('submit', function (event) {
                event.preventDefault();
                updateGrade();
            });
        }

        function updateGrade() {
            const deptID = document.getElementById('deptID').value;
            const ContentID = document.getElementById('editContentID').value;
            const title = document.getElementById('editTitle').value;
            const caption = document.getElementById('editCaption').value;

            fetch('grades_management.php?action=update&id=' + ContentID, {
                method: 'POST',
                body: JSON.stringify({ title, caption, deptID }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadGrades(deptID);
                    document.getElementById('editModal').style.display = 'none';
                } else {
                    alert('Error updating grade: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function deleteGrade(id) {
            const deptID = new URLSearchParams(window.location.search).get('deptID');

            fetch(`grades_management.php?action=delete&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadGrades(deptID);
                    } else {
                        alert('Error deleting grade');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
