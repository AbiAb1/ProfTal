<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connection.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $grade = $conn->real_escape_string($data['grade']);
    $section = $conn->real_escape_string($data['section']);
    $deptID = $conn->real_escape_string($data['deptID']);

    $sql = "INSERT INTO grades (grade, section, dept_ID) VALUES ('$grade', '$section', '$deptID')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} elseif ($action == 'read' && isset($_GET['deptID'])) {
    $deptID = $conn->real_escape_string($_GET['deptID']);

    $sql = "SELECT * FROM grades WHERE dept_ID = '$deptID'";
    $result = $conn->query($sql);

    $grades = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $grades[] = [
                'grades_ID' => $row['grades_ID'],
                'grade' => $row['grade'],
                'section' => $row['section'],
                'dept_ID' => $row['dept_ID']
            ];
        }
    }
    echo json_encode(['grades' => $grades]);
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "DELETE FROM grades WHERE grades_ID = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {
    $data = json_decode(file_get_contents("php://input"), true);

    $grade = $conn->real_escape_string($data['grade']);
    $section = $conn->real_escape_string($data['section']);
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "UPDATE grades SET grade = '$grade', section = '$section' WHERE grades_ID = '$id'";

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
    