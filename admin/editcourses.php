<?php

session_start();
require_once '../config/conn.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

// Edit Course 
$course_id = $_GET['id'];

$edit_course = $conn->query(" SELECT * FROM courses WHERE id = $course_id");

$edit_course = $edit_course->fetch_array(MYSQLI_ASSOC);

// Send data to JavaScript
echo json_encode($edit_course);
