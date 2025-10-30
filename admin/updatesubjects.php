<?php


session_start();
require_once  '../config/conn.php';


if (isset($_POST['update_subject'])) {
    $id = intval($_POST['id'] ?? 0);
    $semester = $_POST['semester'] ?? '';
    $year = $_POST['year'] ?? '';
    $programId = intval($_POST['course'] ?? 0);
    $course_code = $_POST['course_code'] ?? '';
    $descriptive_title = $_POST['descriptive_title'] ?? '';
    $nth = $_POST['nth'] ?? null;
    $units = $_POST['units'] ?? null;
    $lee = $_POST['lee'] ?? null;
    $lab = $_POST['lab'] ?? null;

    if ($id > 0 && $programId > 0 && $course_code !== '' && $descriptive_title !== '' && $year !== '' && $semester !== '') {
        $stmt = $conn->prepare("UPDATE courses SET program_id=?, course_code=?, course_title=?, year_level=?, semester=?, nth=?, units=?, lec=?, lab=? WHERE id=?");
        $stmt->bind_param('isssssiiii', $programId, $course_code, $descriptive_title, $year, $semester, $nth, $units, $lee, $lab, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['year'] = $year . "/ " . $programId . "/ " . $semester;
        $_SESSION['updated'] =  $course_code . " - " . $descriptive_title;
        header("location: subjects.php");
        exit;
    } else {
        $_SESSION['error'] = 'Please complete all required fields.';
        header("location: subjects.php");
        exit;
    }
}
