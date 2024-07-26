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

// Query to fetch profile picture filename from useracc table
$sql = "SELECT * FROM useracc WHERE UserID = $user_id";
$result = mysqli_query($conn, $sql);

// Fetch the profile picture filename
$profile_picture = '';
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $profile_picture = $row['profile'];
    $full_name = $row['fname'] . ' ' . $row['mname'] . '. ' . $row['lname'];
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $bday = $row['bday'];
    $age = $row['age'];
    $sex = $row['sex'];
    $address = $row['address'];
    $email = $row['email'];
    $uname = $row['Username'];
    $password = $row['Password']; 
}

// Construct the full path to the profile picture
$profile_picture_path = 'img/UserProfile/' . $profile_picture;

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>AdminSite</title>
    <style>
        .main-content {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

        .colored-section {
            background-color: #9B2035;
            flex: 1;
            margin: -25px;
        }

        .plain-section {
            flex: 1;
            padding: 20px;
        }

        .overlay {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            justify-content: space-between;
            align-items: center;
        }

        .container-content {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            z-index: 10;
            padding: 50px;
            border-radius: 8px;
            width: 100%;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #fff;
            position: relative;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            position: relative;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn-custom {
            background-color: #9B2035;
            color: white;
            border: none;
            padding: 5px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 50px;
        }

        .btn-edit {
            background-color: #9B2035;
            border-radius: 50%;
            padding: 5px;
            width: 35px;
            height: 35px;
            color: white;
        }

        .full-name {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        
        /* Adjusted styling for left-aligned labels */
        .input-container {
            margin-bottom: 15px;
            text-align: left; /* Ensure text alignment for labels */
        }
        
        .input-container label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left; /* Align label text to the left */
        }
        
        .input-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }

        .modal-content {
            margin: auto;
        }
        /* Ensure modal takes up full width on smaller screens */
        .modal-dialog.custom-modal {
        max-width: 900px; /* Adjust as needed */
        margin: 1.75rem auto; /* Adjust vertical margin if needed */
    }

    .modal-body.d-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-illustration {
        flex: 1;
        background: url("assets/images/passw.png") no-repeat center center;
        background-size: cover;
        height: 100%;
        min-height: 500px; /* Ensures a minimum height if the content is smaller */
        /* Optional: Add border or other styling for debugging */
       
    }

    .modal-form {
        flex: 1;
        padding: 20px;
        box-sizing: border-box;
    }

    .modal-form img {
        width: 100px;
        margin-bottom: 20px;
    }

    .modal-form h2 {
        margin-bottom: 20px;
        font-size: 28px;
        font-weight: 600;
        color: #333;
    }

    .modal-form .progress-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
        background-color:transparent;
    }

    .modal-form .progress-container {
        display: flex;
        align-items: center;
        width: 100%;
        position: relative;
        background-color:transparent;
    }

    .modal-form .progress-step {
        width: 40px;
        height: 40px;
        background-color: #D3D3D3;
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }

    .modal-form .progress-step.completed {
        background-color: #9B2035;
    }

    .modal-form .progress-line {
        height: 4px;
        flex-grow: 1;
        background-color: #ddd;
        position: relative;
        top: 50%;
        transform: translateY(-50%);
        z-index: 0;
        margin: 0 10px;
    }

    .modal-form .progress-line.completed {
        background-color: #861c2e;
    }

    .modal-form p {
        margin-bottom: 20px;
        font-size: 14px;
        color: #777;
    }

    .modal-form input {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        font-size: 16px;
    }

    .modal-form button {
        width: 100%;
        padding: 12px;
        background-color: #9B2035;
        border: none;
        border-radius: 90px;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        display: flex;
        justify-content: center; /* Center the button text */
    }

    .modal-form button:hover {
        background-color: #861c2e;
    }
    .award-container {
            display: flex;
            align-items: center;
            border-radius: 10px;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
            width: 100%;
            position: relative;
            background-color: #f0f0f0;
        }
        .award-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #9b2035;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-right: 15px;
        }
        .award-icon {
            font-size: 24px;
            color: #fff;
        }
        .award-text {
            font-size: 16px;
            font-weight:bold;
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
            <div class="main-content">
                <div class="colored-section"></div>
                <div class="plain-section"></div>
                <div class="overlay">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-5 mt-5">
                                <!-- First Container Content -->
                                <div class="container-content">
                                    <?php if ($profile_picture): ?>
                                        <div class="profile-container">
                                            <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="profile-picture" id="profile-picture">
                                        </div>
                                    <?php else: ?>
                                        <p>No profile picture available.</p>
                                    <?php endif; ?>
                                    <div class="button-group">
                                        <a href="#" class="btn-edit" data-toggle="modal" data-target="#uploadModal" id="btnedit">
                                            <i class='bx bxs-edit'></i>
                                        </a>
                                        <a href="#" class="btn-custom" data-toggle="modal" data-target="#changePasswordModal">
                                            Change Credentials
                                        </a>
                                    </div>
                                    <div class="full-name"><?php echo $full_name; ?></div>
                                    <p style="margin:20px; font-weight:bold;">Your Awards</p>
                                    <!-- Award Containers -->
                                      <!-- Award Containers -->
                                    <div class="award-container">
                                        <div class="award-icon-container">
                                            <i class='bx bx-award award-icon'></i>
                                        </div>
                                        <span class="award-text">Exemplary Contributor </span>
                                    </div>
                                    <div class="award-container">
                                        <div class="award-icon-container">
                                            <i class='bx bx-upload award-icon'></i>
                                        </div>
                                        <span class="award-text">Top Uploader </span>
                                    </div>
                                   
                                </div>
                            </div>
                            <div class="col-md-7 mt-5">
                                <!-- Second Container Content -->
                                <div class="container-content">
                                    <h2>User Information</h2>
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6 input-container">
                                                <label for="uname">Username</label>
                                                <input type="text" id="uname" name="uname" value="<?php echo $uname; ?>" readonly>
                                            </div>
                                            <div class="col-md-6 input-container">
                                                <label for="pass">Password</label>
                                                <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 input-container">
                                                <label for="email">Email</label>
                                                <input type="text" id="email" name="email" value="<?php echo $email; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 input-container">
                                                <label for="fname">First Name</label>
                                                <input type="text" id="fname" name="fname" value="<?php echo $fname; ?>" readonly>
                                            </div>
                                            <div class="col-md-4 input-container">
                                                <label for="mname">Middle Inital</label>
                                                <input type="text" id="mname" name="mname" value="<?php echo $mname; ?>" readonly>
                                            </div>
                                            <div class="col-md-4 input-container">
                                                <label for="lname">Last Name</label>
                                                <input type="text" id="lname" name="lname" value="<?php echo $lname; ?>" readonly>
                                            </div>
                                        </div>
                                        
                                       <div class="row">
                                            <div class="col-md-4 input-container">
                                                <label for="bday">Birthday</label>
                                                <input type="text" id="bday" name="bday" value="<?php echo $bday; ?>" readonly>
                                            </div>

                                            <div class="col-md-4 input-container">
                                                <label for="age">Age</label>
                                                <input type="text" id="age" name="age" value="<?php echo $age; ?>" readonly>
                                            </div>

                                            <div class="col-md-4 input-container">
                                                <label for="sex">Sex</label>
                                                <input type="text" id="sex" name="sex" value="<?php echo $sex; ?>" readonly>
                                            </div>
                                       </div>
                                       
                                       <div class="row">
                                            <div class="col-md-12 input-container">
                                                <label for="address">Address</label>
                                                <input type="text" id="address" name="address" value="<?php echo $address; ?>" readonly>
                                            </div>
                                       </div>      
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for Upload -->
                <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadModalLabel">Upload Profile Picture</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="file">Choose file</label>
                                        <input type="file" class="form-control-file" id="file" name="file">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="uploadBtn">Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal for Change Password -->
                
    <!-- Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog custom-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex">
                    <!-- Illustration on the left -->
                    <div class="modal-illustration"></div>

                    <!-- Form on the right -->
                    <div class="modal-form">
                        <img src="img/Logo/LOGO.png" alt="Logo">
                        <h2>Change Credentials</h2>
                        <div class="progress-bar">
                            <div class="progress-container">
                                <div class="progress-step completed">1</div>
                                <div class="progress-line "></div>
                                <div class="progress-step">2</div>
                                <div class="progress-line"></div>
                                <div class="progress-step">3</div>
                            </div>
                        </div>
                        <p>Enter your email address to receive an OTP.</p>
                        <form action="changePasswordForm" method="POST">
                            <input type="hidden" id="userId" value="<?php echo $_SESSION['user_id']; ?>">

                            <input type="email" name="email" placeholder="Your email address" required>
                            <button type="submit" id="changePasswordBtn">Send OTP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- NAVBAR -->
    <script src="assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
$(document).ready(function () {
    $('#uploadBtn').click(function (e) {
        e.preventDefault();
        var formData = new FormData($('#uploadForm')[0]);

        $.ajax({
            url: 'picupload.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#profile-picture').attr('src', 'img/UserProfile/' + data.filename);
                    $('#uploadModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Profile picture updated successfully.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload failed',
                    text: textStatus
                });
            }
        });
    });

    $('#changePasswordBtn').click(function (e) {
    e.preventDefault();
    var email = $('#email').val();
    var userId = $('#userId').val(); // Assuming you have a hidden input for userId

    $.ajax({
        url: 'changepassword.php',
        type: 'POST',
        data: { email: email, userId: userId },
        success: function (response) {
            if (response.status === 'success') {
                Swal.fire({
                    title: 'OTP Sent',
                    text: response.message,
                    icon: 'success'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'verify_otp2.php?email=' + response.email + '&userId=' + userId;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            Swal.fire({
                icon: 'error',
                title: 'Request failed',
                text: textStatus
            });
        }
    });
});

});


    </script>
</body>

</html>

