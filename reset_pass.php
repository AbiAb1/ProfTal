<?php
// Start session and error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection setup
$dsn = 'mysql:host=localhost;dbname=proftal';
$db_username = 'root';
$db_password = '';

// Initialize email variable
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';

    // Validate password strength (simple example)
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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

            // Update password in the database
            $sql = 'UPDATE users SET password = :password WHERE user_ID = :user_ID';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':user_ID', $userId);
            $stmt->execute();

            // Remove OTP entries for the user
            $sql = 'DELETE FROM OTP WHERE user_ID = :user_ID';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_ID', $userId);
            $stmt->execute();

            echo "<script>
                    alert('Your password has been updated successfully.');
                    window.location.href = 'login.php';
                  </script>";

        } else {
            echo "<script>alert('No account found with that email address.');</script>";
        }

    } catch (PDOException $e) {
        file_put_contents('pdo_error_log.txt', $e->getMessage(), FILE_APPEND);
        echo "<script>alert('An error occurred. Please try again later.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .reset-password-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        .reset-password-container h2 {
            margin-bottom: 15px;
            font-size: 24px;
        }

        .reset-password-container p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }

        .reset-password-container input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .reset-password-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .reset-password-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        <p>Enter a new password for your account.</p>
        <form action="reset_pass.php" method="POST">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <input type="password" name="password" placeholder="New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
