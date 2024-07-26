<?php

// Database connection
include 'connection.php';

// Function to map MIME types to FontAwesome icons
function getIconForMimeType($mimeType) {
    $icon = 'file'; // Default icon if MIME type not matched

    // Mapping MIME types to FontAwesome icons
    $mimeTypeIcons = [
        'application/pdf' => 'fa-file-pdf',
        'application/msword' => 'fa-file-word',
        'application/vnd.ms-excel' => 'fa-file-excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
        'image/jpeg' => 'fa-file-image',
        'image/png' => 'fa-file-image',
        'text/plain' => 'fa-file-alt',
        // Add more MIME types and corresponding icons as needed
    ];

    if (array_key_exists($mimeType, $mimeTypeIcons)) {
        $icon = $mimeTypeIcons[$mimeType];
    }

    return $icon;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Document Management</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://unpkg.com/ionicons@5.5.2/dist/ionicons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            overflow: hidden;
            display: flex;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            margin-top: -10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .header h1 {
            color: #333;
            margin: 0;
            font-size: 1.5rem;
        }

        .search-filter {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .search-filter input,
        .search-filter select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 48%;
        }

        .folder-list {
            display: flex;
            flex-wrap: wrap; /* Allows wrapping to the next line */
            gap: 10px;
            flex: 2; /* Allow list to take up more space */
        }

        .folder-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 200px; /* Adjust size as needed */
            justify-content: center;
            cursor: pointer; /* Indicate clickable */
        }

        .folder-item .icon {
            font-size: 48px;
            margin-right: 15px;
            color: #9B2035;
        }

        .folder-item h3 {
            margin: 0;
            text-align: center;
            font-size: 16px;
            font-weight: normal;
        }

        .folder-details {
            flex: none; /* Allow panel to float */
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: none; /* Hide by default */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed; /* Fixed position */
            top: 55%;
            left: 90%;
            transform: translate(-50%, -50%); /* Center it */
            z-index: 10; /* Ensure it appears above other content */
            max-width: 300px;
            max-height: 500px;
            width: 100%;
            height: 100%;
        }

        /* Overlay to darken the background */
        .overlay {
            display: none; /* Hide by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9; /* Below the details panel but above other content */
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
                <h1>Folder Management</h1>
            </div>
            <div class="container">
                <div class="search-filter">
                    <input type="text" placeholder="Search by name or title" id="search">
                    <select id="filter-status">
                        <option value="">Filter by status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="needs-revision">Needs Revision</option>
                    </select>
                </div>

                <div class="folder-list">
                    <!-- Folder items will be injected here by JavaScript -->
                </div>
            </div>
        </main>
    </section>

    <div class="overlay"></div>
    <div class="folder-details"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Example data - replace with dynamic data from your database
        const folders = [
            { id: 1, name: 'Folder 1', size: '5.4 MB', dateCreated: '2023-01-10', dateModified: '2023-01-15', owner: 'John Doe' },
            { id: 2, name: 'Folder 2', size: '3.1 MB', dateCreated: '2023-02-12', dateModified: '2023-02-13', owner: 'Jane Smith' },
            { id: 3, name: 'Folder 3', size: '8.2 MB', dateCreated: '2023-03-15', dateModified: '2023-03-16', owner: 'Alice Johnson' },
            { id: 4, name: 'Folder 4', size: '2.4 MB', dateCreated: '2023-04-18', dateModified: '2023-04-20', owner: 'Bob Brown' },
            { id: 5, name: 'Folder 5', size: '7.0 MB', dateCreated: '2023-05-10', dateModified: '2023-05-12', owner: 'Charlie Davis' }
        ];

        function displayFolders(folders) {
            const folderList = document.querySelector('.folder-list');
            folderList.innerHTML = '';
            folders.forEach(folder => {
                folderList.innerHTML += `
                    <div class="folder-item" data-id="${folder.id}">
                        <i class="fas fa-folder icon"></i>
                        <h3>${folder.name}</h3>
                    </div>
                `;
            });

            // Add event listeners to folder items
            document.querySelectorAll('.folder-item').forEach(item => {
                item.addEventListener('click', function() {
                    const folderId = this.getAttribute('data-id');
                    // Navigate to another PHP file with folderId as a query parameter
                    window.location.href = `doc_prototype.php?id=${folderId}`;
                });
            });
        }

        function showFolderDetails(id) {
            const folder = folders.find(f => f.id === parseInt(id));
            const detailsDiv = document.querySelector('.folder-details');
            const overlay = document.querySelector('.overlay');
            if (folder) {
                detailsDiv.innerHTML = `
                    <h2>${folder.name}</h2>
                    <p><strong>Size:</strong> ${folder.size}</p>
                    <p><strong>Date Created:</strong> ${folder.dateCreated}</p>
                    <p><strong>Date Modified:</strong> ${folder.dateModified}</p>
                    <p><strong>Owner:</strong> ${folder.owner}</p>
                `;
                detailsDiv.style.display = 'block'; // Show details
                overlay.style.display = 'block'; // Show overlay
            }
        }

        // Hide folder details when the overlay is clicked
        document.querySelector('.overlay').addEventListener('click', function() {
            document.querySelector('.folder-details').style.display = 'none'; // Hide details
            this.style.display = 'none'; // Hide overlay
        });

        // Ensure the details panel hides when clicking outside of it
        document.querySelector('.container').addEventListener('click', function(event) {
            if (!event.target.closest('.folder-item') && !event.target.closest('.folder-details')) {
                document.querySelector('.folder-details').style.display = 'none'; // Hide details
                document.querySelector('.overlay').style.display = 'none'; // Hide overlay
            }
        });

        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredDocs = folders.filter(folder => folder.name.toLowerCase().includes(searchTerm));
            displayDocuments(filteredDocs);
        });

        document.getElementById('filter-status').addEventListener('change', function() {
            const status = this.value;
            const filteredDocs = folders.filter(folder => flder.status === status || status === '');
            displayDocuments(filteredDocs);
        });

        // Display folders on page load
        displayFolders(folders);
    </script>
</body>
</html>
