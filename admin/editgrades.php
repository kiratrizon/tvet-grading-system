<?php
require_once '../config/conn.php';

header('Content-Type: application/json');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM student_grades WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($editgrades = $result->fetch_assoc()) {
        echo json_encode($editgrades, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["error" => "No data found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid ID"]);
}
