<?php
session_start();
require_once '../config/conn.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $sy = $_POST['sy'];
    $schedule_day = $_POST['schedule_day'];
    $schedule_time_start = $_POST['schedule_time_start'];
    $schedule_time_end = $_POST['schedule_time_end'];

    $assigned_at = date('Y-m-d h:i A');

    // CHECK IF TEACHER ALREADY HAS THE SUBJECT
    $check_subject = $conn->query("SELECT * FROM teacher_subjects WHERE teacher_id='$teacher_id' AND subject_id='$subject_id'");
    if ($check_subject->num_rows > 0) {
        $_SESSION['error'] = "This subject is already assigned to the teacher.";
        header("location: asignteacher.php");
        exit;
    }

    // CHECK FOR SCHEDULE CONFLICT
    $check_schedule = $conn->query("
        SELECT * FROM teacher_subjects 
        WHERE teacher_id = '$teacher_id' 
        AND schedule_day = '$schedule_day'
        AND (
            ('$schedule_time_start' BETWEEN schedule_time_start AND schedule_time_end) 
            OR 
            ('$schedule_time_end' BETWEEN schedule_time_start AND schedule_time_end)
            OR 
            (schedule_time_start BETWEEN '$schedule_time_start' AND '$schedule_time_end')
        )
    ");

    if ($check_schedule->num_rows > 0) {
        $_SESSION['error'] = "Schedule conflict detected! The teacher already has a class on $schedule_day from $schedule_time_start to $schedule_time_end.";
        header("location: asignteacher.php");
        exit;
    }

    // INSERT NEW ASSIGNMENT
    $conn->query("
        INSERT INTO `teacher_subjects` (`teacher_id`, `subject_id`, `course`, `year_level`, `semester`, `school_year`, `schedule_day`, `schedule_time_start`, `schedule_time_end`, `assigned_date`) 
        VALUES ('$teacher_id','$subject_id','$course','$year_level','$semester','$sy','$schedule_day','$schedule_time_start','$schedule_time_end','$assigned_at')
    ");

    $_SESSION['success'] = "Teacher assigned successfully!";
    header("location: asignteacher.php");
}
