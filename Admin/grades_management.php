<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connection.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Generate ContentID (6-digit random number)
    $contentID = mt_rand(100000, 999999); // Generates a random 6-digit number

    // Generate ContentCode (6-character random string)
    $contentCode = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);

    $title = $conn->real_escape_string($data['title']);
    $caption = $conn->real_escape_string($data['caption']);
    $deptID = $conn->real_escape_string($data['deptID']);

    $sql = "INSERT INTO feedcontent (ContentID, ContentCode, Title, Captions, dept_ID) 
            VALUES ('$contentID', '$contentCode', '$title', '$caption', '$deptID')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} elseif ($action == 'read' && isset($_GET['deptID'])) {
    $deptID = $conn->real_escape_string($_GET['deptID']);

    $sql = "SELECT * FROM feedcontent WHERE dept_ID = '$deptID'";
    $result = $conn->query($sql);

    $feedcontent = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $feedcontent[] = [
                'ContentID' => $row['ContentID'],
                'Title' => $row['Title'],
                'Captions' => $row['Captions'],
                'dept_ID' => $row['dept_ID']
            ];
        }
    }
    echo json_encode(['feedcontent' => $feedcontent]);
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "DELETE FROM feedcontent WHERE ContentID = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {
    $data = json_decode(file_get_contents("php://input"), true);

    $title = $conn->real_escape_string($data['Title']);
    $caption = $conn->real_escape_string($data['Captions']);
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "UPDATE feedcontent SET Title = '$title', Captions = '$caption'  WHERE ContentID = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'invalid action']);
}

$conn->close();
?>
