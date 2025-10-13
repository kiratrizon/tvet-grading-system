<?php


session_start();
require_once  '../config/conn.php';


if (isset($_POST['submit_subject'])) {
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $course = $_POST['course'];
    $course_code = $_POST['course_code'];
    $descriptive_title = $_POST['descriptive_title'];
    $nth = $_POST['nth'];
    $units = $_POST['units'];
    $lee = $_POST['lee'];
    $lab = $_POST['lab'];
    $covered_qualification = $_POST['covered_qualification'];
    $pre_requisite = $_POST['pre_requisite'];


    $conn->query("INSERT INTO `subjects`(`s_semester`, `s_course_code`, `s_descriptive_title`, `s_nth`, `s_units`, `s_lee`, `s_lab`, `s_covered_qualification`, `s_pre_requisite`, `s_year_level`, `s_course`) VALUES ('$semester','$course_code','$descriptive_title','$nth','$units','$lee','$lab','$covered_qualification','$pre_requisite','$year','$course')");

    $_SESSION['success'] = $course_code . " - " . $descriptive_title;
    header("location: subjects.php");
}
