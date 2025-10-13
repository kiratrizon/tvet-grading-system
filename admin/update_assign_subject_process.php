<?php
session_start();
require_once '../config/conn.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id = $_POST['id'];
    $teacher_id = $_POST['teacher_name'];
    $subject_id = $_POST['subject_id'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $sy = $_POST['sy'];


    $schedule_day = $_POST['schedule_day'];
    $schedule_time_start = $_POST['schedule_time_start'];
    $schedule_time_end = $_POST['schedule_time_end'];


    $updated_at = date('Y-m-d h:i A');

    // echo "<pre>";
    // echo "Teacher ID: " . $teacher_id . "<br>";
    // echo "Subject ID: " . $subject_id . "<br>";
    // echo "Course: " . $course . "<br>";
    // echo "Year Level: " . $year_level . "<br>";
    // echo "Semester: " . $semester . "<br>";
    // echo "School Year: " . $sy . "<br>";
    // echo "Updated At: " . $updated_at . "<br>";
    // echo "</pre>";

    // echo "UPDATE teacher_subjects 
    //                   SET teacher_id='$teacher_id', subject_id='$subject_id', course='$course',  year_level='$year_level', semester='$semester', school_year='$sy', assigned_date='$updated_at' 
    //                   WHERE id = $id";
    // exit;


    // Update record if exists
    $sql = "UPDATE teacher_subjects 
            SET teacher_id='$teacher_id', 
                subject_id='$subject_id', 
                course='$course',  
                year_level='$year_level', 
                semester='$semester', 
                school_year='$sy', 
                assigned_date='$updated_at',
                schedule_day='$schedule_day',
                schedule_time_start='$schedule_time_start',
                schedule_time_end='$schedule_time_end'
            WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Subject assignment updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating record: " . $conn->error;
    }

    header("location: asignteacher.php");
    exit();
}
