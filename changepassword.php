<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Make sure PHPMailer is correctly installed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to perform this action.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $user_id = $_SESSION['user_id'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
        exit;
    }

    // Database connection setup
    $mysqli = new mysqli('localhost', 'root', '', 'proftal');

    // Check connection
    if ($mysqli->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit;
    }

    // Check if email matches the user ID
    $sql = 'SELECT ID FROM useracc WHERE email = ? AND UserID = ?';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('si', $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $userId = $user['ID'];

        // Generate a unique OTP
        $otp = rand(100000, 999999);
        $createdAt = date("Y-m-d H:i:s");

        // Insert OTP into the database
        $sql = 'INSERT INTO OTP (otp, created_at, ID) VALUES (?, ?, ?)';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ssi', $otp, $createdAt, $userId);
        $stmt->execute();

        // Send OTP email using PHPMailer
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'proftal2024@gmail.com';                // SMTP username
            $mail->Password   = 'ytkj saab gnkb cxwa';                  // SMTP password (App Password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
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
            echo json_encode(['status' => 'success', 'message' => 'An OTP has been sent to your email address.', 'email' => $email]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No account found with that email address for the current user.']);
    }

    // Close the statement and connection
    $stmt->close();
    $mysqli->close();
}
?>
