<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $school_year = $_POST['sy'];
    $room = trim($_POST['room'] ?? '');

    $assigned_at = date('Y-m-d h:i A');

    // Insert a single offering with room stored in section
    $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, course, section, year_level, semester, school_year, assigned_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissssss', $teacher_id, $subject_id, $course, $room, $year_level, $semester, $school_year, $assigned_at);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Saved!";
    } else {
        $_SESSION['error'] = "Failed to save.";
    }
    $stmt->close();

    header("location: asignteacher.php");
}
