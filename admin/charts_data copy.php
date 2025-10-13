<?php
session_start();
require_once '../config/conn.php';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}

$response = [];

// Get start_year and end_year from the request
$start_year = isset($_GET['start_year']) ? intval($_GET['start_year']) : null;
$end_year = isset($_GET['end_year']) ? intval($_GET['end_year']) : null;

// Total Students
$query = "SELECT COUNT(*) AS total FROM student_grades";
if ($start_year && $end_year) {
    $query .= " WHERE school_year BETWEEN $start_year AND $end_year";
}
$result = mysqli_query($conn, $query);
$total = mysqli_fetch_assoc($result)['total'];

// Grade Distribution (Passed / Failed)
$query = "SELECT COUNT(*) AS passed FROM student_grades WHERE final_rating BETWEEN 1.0 AND 3.5";
if ($start_year && $end_year) {
    $query .= " AND school_year BETWEEN $start_year AND $end_year";
}
$result = mysqli_query($conn, $query);
$passed = mysqli_fetch_assoc($result)['passed'];

$query = "SELECT COUNT(*) AS failed FROM student_grades WHERE final_rating >= 4.0";
if ($start_year && $end_year) {
    $query .= " AND school_year BETWEEN $start_year AND $end_year";
}
$result = mysqli_query($conn, $query);
$failed = mysqli_fetch_assoc($result)['failed'];

// Courses & Student Count (with Year Level)
$query = "
    SELECT 
        CASE 
            WHEN course = 'DIT' THEN 'Diploma in Information Technology'
            WHEN course = 'DIST' THEN 'Diploma in Information Systems Technology'
            WHEN course = 'DBOT' THEN 'Diploma in Business Office Technology'
            WHEN course = 'DSOT' THEN 'Diploma in Software Technology'
            ELSE course
        END AS course_full,
        year_level, semester, school_year, COUNT(DISTINCT name) AS student_count
    FROM student_grades
";

if ($start_year && $end_year) {
    $query .= " WHERE school_year BETWEEN $start_year AND $end_year";
}
$query .= " GROUP BY course, year_level, semester, school_year";

$result = mysqli_query($conn, $query);
$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Assign colors based on course
    $color = '';
    switch ($row['course_full']) {
        case 'Diploma in Information Technology':
            $color = '#5B0C18'; // RED
            break;
        case 'Diploma in Information Systems Technology':
            $color = '#073F2A'; // GREEN
            break;
        case 'Diploma in Business Office Technology':
            $color = '#D59A03'; // YELLOW
            break;
        case 'Diploma in Software Technology':
            $color = '#01214F'; // BLUE
            break;
        default:
            $color = '#CCCCCC'; // DEFAULT GRAY
    }
    $row['color'] = $color;
    $courses[] = $row;
}

$response['grades'] = [
    'passed' => $passed,
    'failed' => $failed
];
$response['courses'] = $courses;

echo json_encode($response);
