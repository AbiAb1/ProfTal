<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connection.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $conn->real_escape_string($data['name']);
    $info = $conn->real_escape_string($data['info']);

    $sql = "INSERT INTO department (dept_name, dept_info) VALUES ('$name', '$info')";
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
                'dept_info' => $row['dept_info']
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
} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $conn->real_escape_string($data['id']);
    $name = $conn->real_escape_string($data['name']);
    $info = $conn->real_escape_string($data['info']);

    $sql = "UPDATE department SET dept_name = '$name', dept_info = '$info' WHERE dept_ID = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'invalid action']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_GET['action'] === 'getName') {
        $deptID = $_GET['deptID'];
        // Example SQL query to fetch department name based on deptID
        $sql = "SELECT dept_name FROM department WHERE dept_ID = ?";
        
        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$deptID]);
        
        // Fetch the department name
        $departmentName = $stmt->fetchColumn(); // Assuming departmentName is a column in your departments table
        
        if ($departmentName) {
            $response = ['status' => 'success', 'dept_name' => $departmentName];
            echo json_encode($response);
        } else {
            $response = ['status' => 'error', 'message' => 'Department name not found'];
            echo json_encode($response);
        }
    }
    // Add other actions if needed, like updating or deleting departments
} else {
    // Handle unsupported HTTP methods
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}

$conn->close();
?>
