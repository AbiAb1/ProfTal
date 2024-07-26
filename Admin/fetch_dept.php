<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "connection.php";

header('Content-Type: application/json');

$sql = "SELECT * FROM department";
$result = $conn->query($sql);

$departments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = [
            'dept_ID' => $row['dept_ID'],
            'dept_name' => $row['dept_name'],
            'dept_info' => $row['dept_info']
        ];
    }
}
echo json_encode(['department' => $departments]);

$conn->close();
?>
