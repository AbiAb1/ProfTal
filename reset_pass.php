<?php
// Start session and error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection setup
$mysqli = new mysqli('localhost', 'root', '', 'proftal');

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize email variable
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';

    // Validate password strength
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
        exit;
    }

    // Get the user ID from email
    $sql = 'SELECT ID FROM useracc WHERE email = ?';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $userId = $user['ID'];

        // Update password in the database
        $sql = 'UPDATE useracc SET Password = ? WHERE ID = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('si', $password, $userId);
        $stmt->execute();

        // Remove OTP entries for the user
        $sql = 'DELETE FROM OTP WHERE ID = ?';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        echo "<script>
                alert('Your password has been updated successfully.');
                window.location.href = 'index.php';
              </script>";

    } else {
        echo "<script>alert('No account found with that email address.');</script>";
    }

    // Close the statement and connection
    $stmt->close();
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            transition: background-color 0.5s;
        }

        .container {
            display: flex;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            height: 500px;
           
        }

        .illustration {
            width: 50%;
            background: url("assets/images/passw.png") no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .forgot-password-container {
            padding: 60px;
            width: 50%;
            box-sizing: border-box;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .forgot-password-container img {
            width: 100px;
            margin-bottom: 20px;
            padding-left:110px;
        }

        .forgot-password-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 600;
            color: #333;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
        }

        .progress-container {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }

        .progress-step {
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

        .progress-step.completed {
            background-color: #9B2035;
        }

        .progress-line {
            height: 4px;
            flex-grow: 1;
            background-color: #ddd;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            z-index: 0;
            margin: 0 10px;
        }

        .progress-line.completed {
            background-color: #861c2e;
        }

        .forgot-password-container p {
            margin-bottom: 20px;
            font-size: 14px;
            color: #777;
        }

        .forgot-password-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 16px;
        }

        .forgot-password-container button {
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
            margin-left:15px;
        }

        .forgot-password-container button:hover {
            background-color: #861c2e;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="container">
        
        <div class="illustration"></div>
        <div class="forgot-password-container">
            <img src="img/Logo/LOGO.png" alt="Logo">
            <h2>Password Recovery</h2>
            <div class="progress-bar">
                <div class="progress-container">
                    <div class="progress-step completed">1</div>
                    <div class="progress-line completed "></div>
                    <div class="progress-step completed">2</div>
                    <div class="progress-line completed"></div>
                    <div class="progress-step completed">3</div>
                   
                </div>
            </div>
            <p>Enter your new password. <br><span style="font-weight:bold;">Note: It must be 6 characters or more.</span></p>
            <form action="reset_pass.php" method="POST">
                <input type="hidden" name="email" value="<?php echo $email; ?>">
                <input type="password" name="password" placeholder="New Password" required>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>