<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $stmt = $conn->prepare("SELECT UserID, Username, Password FROM useracc WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userID, $db_username, $db_password);

    if ($stmt->fetch()) {
        // Check if the entered password matches the database password (not hashed)
        if ($password === $db_password) {
            $_SESSION['user_id'] = $userID;
            $_SESSION['username'] = $db_username;
            header("Location: dash_admin.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username.";
    }

    $stmt->close();
    $conn->close();
}
?>
