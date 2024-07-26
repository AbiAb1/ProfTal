<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
            font-family: 'Poppins', sans-serif;
            background-image: url("assets/images/portfolio-left-dec.jpg"), url("assets/images/portfolio-right-dec.jpg");
        }

        .container {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 900px;
            max-width: 100%;
        }

        .illustration {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-right: 80px;
        }

        .illustration img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .registration-form {
            flex: 1;
           
        }

        .registration-form h2 {
            margin-bottom: 20px;
            font-size: 28px;
            text-align: flex;
            color: #9B2035;
            font-weight: 600;
        }

        .registration-form .form-group {
            margin-bottom: 15px;
        }

        .registration-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 400;
        }

        .registration-form input,
        .registration-form select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
        }

        .registration-form button {
    width: 100%;
    padding: 10px;
    background-color: #007bff; /* Default background color */
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
    font-family: 'Poppins', sans-serif;
}

.registration-form button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.registration-form button:hover:not(:disabled) {
    background-color: #0056b3;
}

.registration-form button:active:not(:disabled) {
    background-color: #9B2035 !important; /* Background color when active */
    box-shadow: inset 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds a shadow effect */
}


        .name-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .name-group .form-group {
            flex: 1;
        }

                .checkbox-list {
            white-space: 
        }
        .certification1 .certification2 {
            vertical-align: top;
            display:inline-block;
            
        }
        .cert1_label .cert2_label{
            display: inline;
            white-space: normal; /* Allow text to wrap */
            vertical-align: middle; /* Align text with checkbox */
            line-height: 1.5; /* Improve text alignment */
           
            box-sizing: border-box; /* Ensure padding is included in width */
        }
        label {
            display: contents!important;
             font-size: 16px;
             
            }
        input[type="checkbox"] {
            display: inline-block;
            width: auto;
            vertical-align: middle;
        }
        </style>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container">
    <div class="illustration">
        <img src="assets/images/Sign-up-amico.png" alt="Illustration">
    </div>
    <div class="registration-form">
        <h2>Register</h2>
        <form id="registration-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="name-group">
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="middleinitial">M.I.:</label>
                    <input type="text" id="middleinitial" name="middleinitial" maxlength="1" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
            </div>
            <div class="name-group">
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday" required onchange="calculateAge()">
                </div>
            </div>
            <div class="name-group">
                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" readonly required >
                </div>
               <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div> 
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" cols="40" row="4"required>
                </div>
            <div class="checkbox-list">
                <input type="checkbox" id="certification1" name="certification1" class="certification1" required>
                <label for="certification1">I certify that all information provided is correct.</label><br>
                <input type="checkbox" id="certification2" name="certification2" class="certification2" required>
                <label for="certification2">I understand that this will be approved by the admin before logging in.</label>
            </div>
            <button type="submit" id="register-button" disabled style="margin-top:20px;border-radius:90px;background-color:;">Register</button>
        </form>
    </div>
</div>

<script>
    function calculateAge() {
        const birthdayInput = document.getElementById('birthday');
        const ageInput = document.getElementById('age');
        const birthday = new Date(birthdayInput.value);
        const today = new Date();

        if (birthdayInput.value) {
            let age = today.getFullYear() - birthday.getFullYear();
            const monthDiff = today.getMonth() - birthday.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                age--;
            }

            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    }

    function validateForm() {
        const form = document.getElementById('registration-form');
        const registerButton = document.getElementById('register-button');
        
        const firstname = document.getElementById('firstname').value;
        const middleinitial = document.getElementById('middleinitial').value;
        const lastname = document.getElementById('lastname').value;
        const gender = document.getElementById('gender').value;
        const birthday = document.getElementById('birthday').value;
        const age = document.getElementById('age').value;
        const address = document.getElementById('address').value;
        const email = document.getElementById('email').value;
        const certification1 = document.getElementById('certification1').checked;
        const certification2 = document.getElementById('certification2').checked;

        registerButton.disabled = !(firstname && middleinitial && lastname && gender && birthday && age && address && email && certification1 && certification2);
    }

    const formElements = document.querySelectorAll('#registration-form input, #registration-form select');
    formElements.forEach(element => {
        element.addEventListener('input', validateForm);
    });

    document.addEventListener('DOMContentLoaded', validateForm);
</script>

<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    function generateRandomString($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form data
        $firstname = htmlspecialchars($_POST['firstname']);
        $middleinitial = htmlspecialchars($_POST['middleinitial']);
        $lastname = htmlspecialchars($_POST['lastname']);
        $gender = htmlspecialchars($_POST['gender']);
        $birthday = htmlspecialchars($_POST['birthday']);
        $age = htmlspecialchars($_POST['age']);
        $address = htmlspecialchars($_POST['address']);
        $email = htmlspecialchars($_POST['email']);

        // Generate default username and password
        $username = generateRandomString();
        $password = $username; // Use a different length if needed

        // Example department ID and role (update as necessary)
        $role = 'Teacher'; // Default or fetched from another source
        $status = 'pending'; // Default status

        // Database connection setup
        $mysqli = new mysqli('localhost', 'root', '', 'proftal');
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }

        // Insert form data into the `useracc` table
        $stmt = $mysqli->prepare("INSERT INTO useracc (fname, mname, lname, sex, bday, age, address, email, Username, Password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssssssss', $firstname, $middleinitial, $lastname, $gender, $birthday, $age, $address, $email, $username, $password, $role, $status);

        if ($stmt->execute()) {
            echo '<script>
                Swal.fire({
                    title: "Success!",
                   text: "Registration successful. Your account is pending confirmation. You will receive an email when it is verified.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php"; // Redirect to home page or login page
                    }
                });
            </script>';
        } else {
            echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "Failed to register. Please try again later.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            </script>';
        }
        $stmt->close();
        $mysqli->close();
    }
?>


</body>
</html>
