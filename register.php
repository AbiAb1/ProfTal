<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .registration-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .registration-form h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .registration-form .form-group {
            margin-bottom: 15px;
        }
        .registration-form label {
            display: block;
            margin-bottom: 5px;
        }
        .registration-form input,
        .registration-form select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .registration-form button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            opacity: 1;
            transition: background-color 0.3s;
        }
        .registration-form button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .registration-form button:hover:not(:disabled) {
            background-color: #218838;
        }
        .name-group {
            display: flex;
            justify-content: space-between;
        }
        .name-group .form-group {
            flex: 1;
            margin-right: 10px;
        }
        .name-group .form-group:last-child {
            margin-right: 0;
        }
        .name-group .small-input {
            flex: 0 0 60px;
        }
        .name-group .medium-input {
            flex: 0 0 130px;
        }
        .checkbox-list {
            white-space: nowrap
        }
        .certification1 .certification2 {
            vertical-align: top;
            display:inline-block
        }
        .cert1_label .cert2_label{
            display: inline;
            white-space: normal; /* Allow text to wrap */
            vertical-align: middle; /* Align text with checkbox */
            line-height: 1.5; /* Improve text alignment */
            width: calc(100% - 30px); /* Adjust width to fit within container */
            box-sizing: border-box; /* Ensure padding is included in width */
        }
        label {
            display: contents!important;
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

<div class="registration-form">
    <h2>Register</h2>
    <form id="registration-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
    <div class="name-group">
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group small-input">
                <label for="middleinitial">M.I.:</label>
                <input type="text" id="middleinitial" name="middleinitial" maxlength="1" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
        </div>
        <div class="name-group">
            <div class="form-group medium-input">
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
            <div class="form-group small-input">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" readonly required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="checkbox-list">
                <input type="checkbox" id="certification1" name="certification1" class="certification1" required>
                <label for="certificatioin1" class="cert1_label">I certify that all information provided is correct.</p>
                <input type="checkbox" id="certification2" name="certification2" class="certification2" required>
                <label for="certification2" class="cert2_label">I understand that this will be approved by the admin before logging in.</p>
        </div>
        <button type="submit" id="register-button" disabled>Register</button>
    </form>
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
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php';

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
        $password = $username;

        // Example department ID and role (update as necessary)
        $dept_ID = 1; // Default or fetched from another source
        $role = 'Teacher'; // Default or fetched from another source
        $status = 'pending'; // Default status

        // Database connection setup
        $mysqli = new mysqli('localhost', 'root', '', 'proftal');

        // Check connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // Prepare and bind
        $stmt = $mysqli->prepare("INSERT INTO users (username, password, first_name, middle_initial, last_name, gender, birthday, age, address, email, dept_ID, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", $username, $password, $firstname, $middleinitial, $lastname, $gender, $birthday, $age, $address, $email, $dept_ID, $role, $status);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Registration successful! You will receive an email once your registration is approved by an admin.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'login.php'; // Redirect to login or another page
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Error: " . $stmt->error . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }

        // Close the statement and connection
        $stmt->close();
        $mysqli->close();
    }
?>


</body>
</html>
