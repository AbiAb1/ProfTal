<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include your database connection file here
include 'connection.php';

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Query to fetch contents from feedcontent table
$sql = "SELECT fs.ContentID, fs.Title, fs.Captions
        FROM feedcontent fs
        INNER JOIN usercontent uc ON fs.ContentID = uc.ContentID
        WHERE uc.UserID = $user_id AND Status=1";
$result = mysqli_query($conn, $sql);

// Check if there are any records
if (mysqli_num_rows($result) > 0) {
    // Output data of each row
} else {
    echo "No content available.";
}

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects</title>
    <!-- ======= Styles ====== -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .cardBox {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            height: 350px;
        }

        .card {
            width: calc(33.33% - 20px); /* 3 cards per row */
            background-color: #9B2035;
            padding: 20px;
            box-sizing: border-box;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #fff;
        }

        .card p {
            font-size: 14px;
            color: #fff;
        }

        .search-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px; margin-right:20px;
        }

        .search-bar {
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid #ccc;
            width: 250px;
           
        }

        .fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #9B2035;
            color: #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .fab i {
            font-size: 30px;
        }
        .plus-icon {
            font-size: 30px;
            color: black;
            cursor: pointer;
            margin-left:20px;
        }


        .search-container {
        position: relative;
        }

        .input {
        width: 150px;
        padding: 10px 0px 10px 40px;
        border-radius: 9999px;
        border: solid 1px #333;
        transition: all .2s ease-in-out;
        outline: none;
        opacity: 0.8;
        }

        .search-container svg {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translate(0, -50%);
        }

        .input:focus {
        opacity: 1;
        width: 250px;
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
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="title" style="margin-bottom: 20px;">Subjects</h2>
                <div class="search-container">
                <input type="text" placeholder="Search" name="text" class="input"id="searchInput">
                <svg fill="#000000" width="20px" height="20px" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg">
                    <path d="M790.588 1468.235c-373.722 0-677.647-303.924-677.647-677.647 0-373.722 303.925-677.647 677.647-677.647 373.723 0 677.647 303.925 677.647 677.647 0 373.723-303.924 677.647-677.647 677.647Zm596.781-160.715c120.396-138.692 193.807-319.285 193.807-516.932C1581.176 354.748 1226.428 0 790.588 0S0 354.748 0 790.588s354.748 790.588 790.588 790.588c197.647 0 378.24-73.411 516.932-193.807l516.028 516.142 79.963-79.963-516.142-516.028Z" fill-rule="evenodd"></path>
                </svg>
                    <i class='bx bxs-user plus-icon'></i>
                </div>
            </div>
            <div class="cardBox">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    mysqli_data_seek($result, 0); // Reset pointer to the beginning
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='card'>";
                        echo "<h2><a style='color:#ffff;' href='tasks.php?content_id=" . $row['ContentID'] . "'>" . $row['Title'] . "</a></h2>";
                        echo "<p>" . $row['Captions'] . "</p>";
                        // Add more elements as needed (e.g., images, links)
                        echo "</div>";
                    }
                } else {
                    echo "No content available.";
                }
                ?>
            </div>
        </main>
        <!-- MAIN -->
    </section>

    <!-- Floating Action Button -->
    <div class="fab" data-toggle="modal" data-target="#exampleModal">
        <i class='bx bx-plus'></i>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Add your form or content here -->
                    <form>
                        <div class="form-group">
                            <label for="contentCode">Content Code</label>
                            <input type="text" class="form-control" id="contentCode" placeholder="Enter Code">
                        </div>
                        <div class="form-group">
                        </div>
                        <!-- Add more form fields as needed -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveButton" disabled>Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
       $(document).ready(function() {
            $('#contentCode').on('input', function() {
                var code = $(this).val();
                if (code) {
                    $.ajax({
                        url: 'check_code.php',
                        type: 'POST',
                        data: { code: code },
                        success: function(response) {
                            if (response == 'exists') {
                                $('#saveButton').prop('disabled', false);
                            } else {
                                $('#saveButton').prop('disabled', true);
                            }
                        }
                    });
                } else {
                    $('#saveButton').prop('disabled', true);
                }
            });

            $('#saveButton').on('click', function() {
                var code = $('#contentCode').val();
                $.ajax({
                    url: 'insert_usercontent.php',
                    type: 'POST',
                    data: { code: code },
                    success: function(response) {
                        if (response == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Content Added',
                        text: 'Content added successfully.',
                    }).then(() => {
                        $('#exampleModal').modal('hide');
                        location.reload(); // Reload the page to reflect changes
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error adding content.',
                    });
                }
                    }
                });
            });
        });
         // Search functionality
         document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                const title = card.querySelector('h2').innerText.toLowerCase();
                if (title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

    </script>
</body>

</html>
