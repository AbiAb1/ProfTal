<?php
session_start();
include 'connection.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];

    // Update the StatM column to "Offline"
    $stmt = $conn->prepare("UPDATE useracc SET StatusM = 'Offline' WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->close();
}

// Unset all of the session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to index.php after logging out
header("Location: index.php");
exit();
?>
