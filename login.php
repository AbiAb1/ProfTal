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
            $_SESSION['login_success'] = true; // Set session variable

            // Close the previous statement before executing the update
            $stmt->close();

            // Update the StatusM column to "Active Now"
            $updateStmt = $conn->prepare("UPDATE useracc SET StatusM = 'Active Now' WHERE UserID = ?");
            $updateStmt->bind_param("i", $userID);
            $updateStmt->execute();
            $updateStmt->close();

            header("Location: dash.php");
            exit();
        } else {
            $_SESSION['login_success'] = false; // Set session variable for invalid password
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['login_success'] = false; // Set session variable for no user found
        header("Location: index.php");
        exit();
    }

    $stmt->close(); // Close the statement if it's not already closed
    $conn->close();
}
?>
