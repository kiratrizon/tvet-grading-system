<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../index.php");
    exit;
}

$response = [];

// Get start_year and end_year from the request (default values)
$start_year = isset($_GET['start_year']) ? intval($_GET['start_year']) : 2023;
$end_year = isset($_GET['end_year']) ? intval($_GET['end_year']) : 2024;

// Total Students Query
$query = "SELECT COUNT(DISTINCT name) AS total FROM student_grades 
          WHERE CAST(school_year AS UNSIGNED) BETWEEN $start_year AND $end_year";
$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));
$total = mysqli_fetch_assoc($result)['total'];

// Grade Distribution Query
$query = "SELECT 
    COUNT(DISTINCT name) AS total_students,
    COUNT(DISTINCT CASE WHEN final_rating BETWEEN 1.0 AND 3.5 
        AND name NOT IN (SELECT name FROM student_grades WHERE final_rating >= 5.0 OR final_rating = 'INC') 
        THEN name END) AS passed,
    COUNT(DISTINCT CASE WHEN final_rating >= 5.0 THEN name END) AS failed,
    COUNT(DISTINCT CASE WHEN final_rating = 'INC' THEN name END) AS incomplete
    FROM student_grades
    WHERE CAST(school_year AS UNSIGNED) BETWEEN $start_year AND $end_year";

$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));
$row = mysqli_fetch_assoc($result);
$response['grades'] = [
    'passed' => $row['passed'],
    'failed' => $row['failed'],
    'incomplete' => $row['incomplete']
];

// Courses & Student Count Query
$query = "SELECT 
    course, year_level, semester, school_year,
    COUNT(DISTINCT name) AS student_count,
    COUNT(DISTINCT CASE WHEN final_rating BETWEEN 1.0 AND 3.5 
        AND name NOT IN (SELECT name FROM student_grades WHERE final_rating >= 5.0 OR final_rating = 'INC') 
        THEN name END) AS passed,
    COUNT(DISTINCT CASE WHEN final_rating >= 5.0 THEN name END) AS failed,
    COUNT(DISTINCT CASE WHEN final_rating = 'INC' THEN name END) AS incomplete
    FROM student_grades
    WHERE CAST(school_year AS UNSIGNED) BETWEEN $start_year AND $end_year
    GROUP BY course, year_level, semester, school_year";

$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));
$courses = [];
$courseFullNames = [
    'DIT' => 'Diploma in Information Technology',
    'DIST' => 'Diploma in Information Systems Technology',
    'DBOT' => 'Diploma in Business Office Technology',
    'DSOT' => 'Diploma in Software Operations Technology'
];

while ($row = mysqli_fetch_assoc($result)) {
    $color = match ($row['course']) {
        'DIT' => '#5B0C18',
        'DIST' => '#073F2A',
        'DBOT' => '#D59A03',
        'DSOT' => '#01214F',
        default => '#CCCCCC',
    };

    $row['color'] = $color;
    $row['full_course_name'] = $courseFullNames[$row['course']] ?? $row['course'];
    $courses[] = $row;
}

$response['courses'] = $courses;
echo json_encode($response);
