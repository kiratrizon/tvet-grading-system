<?php
session_start();
require_once '../config/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $missing_requirement = $_POST['missing_requirement'];
    $teacher_id = $_SESSION['teacher_id'];
    $studid = $_POST['studid'];
    $studremarks = $_POST['studremarks'];

    $stmt = $conn->prepare("INSERT INTO student_missing_requirements (student_id, subject_id, missing_requirement, flagged_by_teacher,studid, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiis", $student_id, $subject_id, $missing_requirement, $teacher_id, $studid, $studremarks);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Successfully flagged student for missing requirements!";
    } else {
        $_SESSION['error'] = "Failed to flag student. Please try again!";
    }

    header("Location: mysubjects.php");
    exit;
}
