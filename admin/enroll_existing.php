<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a') {
	header('location: ../index.php');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$studentId = intval($_POST['student_id'] ?? 0);
$yearLevel = trim($_POST['year_level'] ?? '');
$schoolYear = trim($_POST['school_year'] ?? '');

	if ($studentId <= 0 || $yearLevel === '' || $schoolYear === '') {
		$_SESSION['error'] = 'Please complete all fields for enrollment.';
		header('location: students_add.php');
		exit;
	}

	// get student's program (course id)
	$student = $conn->query("SELECT course, name FROM student_users WHERE id = $studentId")->fetch_assoc();
	if (!$student) {
		$_SESSION['error'] = 'Student not found.';
		header('location: students_add.php');
		exit;
	}
	$courseId = $conn->real_escape_string($student['course']);

// normalize year level labels (accept 1st/2nd/3rd Year variants)
$map = [
    '1st year' => 'First Year',
    'first year' => 'First Year',
    '2nd year' => 'Second Year',
    'second year' => 'Second Year',
    '3rd year' => 'Third Year',
    'third year' => 'Third Year'
];
$ylKey = strtolower($yearLevel);
$yearLevelNorm = $map[$ylKey] ?? $yearLevel;

    // capacity-aware enroll
    $enrollRes = myTools::autoEnrollStudentToProgramYearSY([
        'conn' => $conn,
        'student_id' => $studentId,
        'course_id' => $courseId,
        'year_level' => $yearLevelNorm,
        'school_year' => $schoolYear,
        'capacity' => env('DEFAULT_SECTION_CAPACITY', 45)
    ]);
    $created = $enrollRes['created'];

if ($created > 0) {
		$_SESSION['success'] = "Enrolled ".$student['name']." into $created subject(s) for $yearLevel, $schoolYear.";
	} else {
	// Provide helpful suggestions of available combinations for this program
	$suggestions = $conn->query("SELECT DISTINCT year_level, school_year FROM teacher_subjects WHERE course = '$courseId' ORDER BY school_year DESC, year_level")->fetch_all(MYSQLI_ASSOC);
	if ($suggestions) {
		$opts = array_map(function($r){ return $r['year_level'].' - '.$r['school_year']; }, $suggestions);
		$_SESSION['error'] = 'No matching subjects found or already enrolled. Available for this program: '.implode('; ', $opts);
	} else {
		$_SESSION['error'] = 'No matching subjects found for this program. Please assign subjects to the program and try again.';
	}
	}
	header('location: students_add.php');
	exit;
}

header('location: students_add.php');
