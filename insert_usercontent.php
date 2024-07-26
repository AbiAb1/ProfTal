<?php
session_start();
include 'connection.php';

if (isset($_POST['code']) && isset($_SESSION['user_id'])) {
    $code = $_POST['code'];
    $user_id = $_SESSION['user_id'];

    // Fetch ContentID from feedcontent based on the given code
    $query = "SELECT ContentID FROM feedcontent WHERE ContentCode = '$code'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $content_id = $row['ContentID'];

        // Insert UserID and ContentID into usercontent table
        $insert_query = "INSERT INTO usercontent (UserID, ContentID,Status) VALUES ('$user_id', '$content_id',1)";
        if (mysqli_query($conn, $insert_query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }

    mysqli_close($conn);
}
?>
