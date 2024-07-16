<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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

        .verify-otp-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        .verify-otp-container h2 {
            margin-bottom: 15px;
            font-size: 24px;
        }

        .verify-otp-container p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }

        .verify-otp-container input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .verify-otp-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .verify-otp-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="verify-otp-container">
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your email address.</p>
        <form action="verify_otp.php" method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>

    <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $otp = htmlspecialchars($_POST['otp']);

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

        // Get the user ID from email
        $sql = 'SELECT user_ID FROM users WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['user_ID'];

            // Check OTP in the database
            $sql = 'SELECT otp, created_at FROM OTP WHERE user_ID = :user_ID ORDER BY ID DESC LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_ID', $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $storedOtp = $result['otp'];
                $createdAt = $result['created_at'];
                $expiry = date("Y-m-d H:i:s", strtotime($createdAt . ' + 10 minutes')); // OTP expires in 10 minutes

                if ($otp === $storedOtp && date("Y-m-d H:i:s") <= $expiry) {
                    // OTP is valid, redirect to password reset page
                    echo "<script>
                            alert('OTP verified successfully.');
                            window.location.href = 'reset_pass.php?email=$email';
                          </script>";
                } else {
                    echo "<script>alert('Invalid OTP or OTP has expired.');</script>";
                }
            } else {
                echo "<script>alert('No OTP found for this email address.');</script>";
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
