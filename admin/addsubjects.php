<?php


session_start();
require_once  '../config/conn.php';


if (isset($_POST['submit_subject'])) {
    $semester = $_POST['semester'] ?? '';
    $year = $_POST['year'] ?? '';
    $programId = intval($_POST['course'] ?? 0);
    $course_code = $_POST['course_code'] ?? '';
    $descriptive_title = $_POST['descriptive_title'] ?? '';
    $nth = $_POST['nth'] ?? null;
    $units = $_POST['units'] ?? null;
    $lee = $_POST['lee'] ?? null;
    $lab = $_POST['lab'] ?? null;

    if ($programId > 0 && $course_code !== '' && $descriptive_title !== '' && $year !== '' && $semester !== '') {
        $stmt = $conn->prepare("INSERT INTO courses (program_id, course_code, course_title, year_level, semester, nth, units, lec, lab) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssssiii', $programId, $course_code, $descriptive_title, $year, $semester, $nth, $units, $lee, $lab);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = $course_code . " - " . $descriptive_title;
        header("location: subjects.php");
        exit;
    } else {
        $_SESSION['error'] = 'Please complete all required fields.';
        header("location: subjects.php");
        exit;
    }
}
