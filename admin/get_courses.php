<?php


session_start();
require_once  '../config/conn.php';


$result = $conn->query("SELECT id, program_name FROM programs");
$courses = [];

while ($row = $result->fetch_assoc()) {
    $courses[$row['id']] = $row['program_name'];
}

echo json_encode($courses);
