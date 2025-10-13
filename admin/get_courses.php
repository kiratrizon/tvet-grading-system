<?php


session_start();
require_once  '../config/conn.php';


$result = $conn->query("SELECT s_course, course_name FROM courses");
$courses = [];

while ($row = $result->fetch_assoc()) {
    $courses[$row['s_course']] = $row['course_name'];
}

echo json_encode($courses);
