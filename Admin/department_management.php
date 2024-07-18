<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connection.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Debug: Print the received data
    error_log(print_r($data, true));

    $name = $conn->real_escape_string($data['name']);
    $info = $conn->real_escape_string($data['info']);

    $sql = "INSERT INTO department (dept_name, dept_info) VALUES ('$name', '$info')";

    // Debug: Print the SQL query
    error_log($sql);

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} elseif ($action == 'read') {
    $sql = "SELECT * FROM department";
    $result = $conn->query($sql);

    $departments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $departments[] = [
                'dept_ID' => $row['dept_ID'],
                'dept_name' => $row['dept_name'],
                'dept_info' => $row['dept_info'],
                'link' => 'grades.php?deptID=' . $row['dept_ID']  // Add the link to the grades page
            ];
        }
    }
    echo json_encode(['department' => $departments]);
} elseif ($action == 'delete') {
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "DELETE FROM department WHERE dept_ID = '$id'";
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
