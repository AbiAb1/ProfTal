<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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

        .forgot-password-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        .forgot-password-container h2 {
            margin-bottom: 15px;
            font-size: 24px;
        }

        .forgot-password-container p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }

        .forgot-password-container input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .forgot-password-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .forgot-password-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <p>Enter your email address to receive an OTP.</p>
        <form action="forgot_pass.php" method="POST">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit">Send OTP</button>
        </form>
    </div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the path to autoload.php is correct
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');</script>";
        exit;
    }

    // Database connection setup
    $dsn = 'mysql:host=localhost;dbname=proftal';
    $db_username = 'root';
    $db_password = '';

    try {
        $pdo = new PDO($dsn, $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if email exists and get user ID
        $sql = 'SELECT user_ID FROM users WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['user_ID'];

            // Generate a unique OTP
            $otp = rand(100000, 999999);
            $createdAt = date("Y-m-d H:i:s");

            // Insert OTP into the database
            $sql = 'INSERT INTO OTP (otp, created_at, user_ID) VALUES (:otp, :created_at, :user_ID)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':otp', $otp);
            $stmt->bindParam(':created_at', $createdAt);
            $stmt->bindParam(':user_ID', $userId);
            $stmt->execute();

            // Send OTP email using PHPMailer
            $mail = new PHPMailer(true); // Passing `true` enables exceptions

            try {
                //Server settings
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'proftal2024@gmail.com';                // SMTP username
                $mail->Password   = 'ytkj saab gnkb cxwa';                    // SMTP password (App Password)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
                $mail->Port       = 587;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('proftal2024@gmail.com', 'ProfTal');
                $mail->addAddress($email);                                  // Add a recipient

                // Content
                $mail->isHTML(true);                                        // Set email format to HTML
                $mail->Subject = 'Your OTP Code';
                $mail->Body    = "Your OTP code is: $otp<br><br>It will expire in 10 minutes.";
                $mail->AltBody = "Your OTP code is: $otp\n\nIt will expire in 10 minutes.";

                $mail->send();
                echo "<script>
                        alert('An OTP has been sent to your email address.');
                        window.location.href = 'verify_otp.php?email=$email';
                      </script>";
            } catch (Exception $e) {
                echo "<script>alert('Failed to send OTP. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('No account found with that email address.');</script>";
        }

    } catch (PDOException $e) {
        file_put_contents('pdo_error_log.txt', $e->getMessage(), FILE_APPEND);
        echo "<script>alert('An error occurred. Please try again later.');</script>";
    }
}
?>
</body>
</html>
