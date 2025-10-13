<?php


session_start();
require_once  '../config/conn.php';


if (isset($_POST['update_subject'])) {
    $id = $_POST['id'];
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


    $conn->query("UPDATE `subjects` SET `s_semester`='$semester',`s_course_code`='$course_code',`s_descriptive_title`='$descriptive_title',`s_nth`='$nth',`s_units`='$units',`s_lee`='$lee',`s_lab`='$lab',`s_covered_qualification`='$covered_qualification',`s_pre_requisite`='$pre_requisite',`s_year_level`='$year',`s_course`='$course' WHERE s_id = $id");

    $_SESSION['year'] = $year . "/ " . $course . "/ " . $semester;
    $_SESSION['updated'] =  $course_code . " - " . $descriptive_title;
    header("location: subjects.php");
}
