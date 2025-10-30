<?php
session_start();
require_once '../config/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 's') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? '';

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS student_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    student_name VARCHAR(255) NULL,
    instructor VARCHAR(255) NULL,
    program VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NULL,
    year_level VARCHAR(100) NOT NULL,
    semester VARCHAR(100) NOT NULL,
    school_year VARCHAR(50) NOT NULL,
    clarity VARCHAR(20) NOT NULL,
    helpfulness VARCHAR(20) NOT NULL,
    organization VARCHAR(20) NOT NULL,
    fairness VARCHAR(20) NOT NULL,
    comments TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Collect inputs (basic sanitization via prepared statements later)
$instructor = $_POST['instructor'] ?? null;
$program = $_POST['program'] ?? '';
$subject = $_POST['subject'] ?? null;
$year_level = $_POST['year_level'] ?? '';
$semester = $_POST['semester'] ?? '';
$school_year = $_POST['school_year'] ?? '';
$clarity = $_POST['clarity'] ?? '';
$helpfulness = $_POST['helpfulness'] ?? '';
$organization = $_POST['organization'] ?? '';
$fairness = $_POST['fairness'] ?? '';
$comments = $_POST['comments'] ?? null;

if ($program === '' || $year_level === '' || $semester === '' || $school_year === '' || $clarity === '' || $helpfulness === '' || $organization === '' || $fairness === '') {
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO student_evaluations (student_id, student_name, instructor, program, subject, year_level, semester, school_year, clarity, helpfulness, organization, fairness, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    'issssssssssss',
    $student_id,
    $student_name,
    $instructor,
    $program,
    $subject,
    $year_level,
    $semester,
    $school_year,
    $clarity,
    $helpfulness,
    $organization,
    $fairness,
    $comments
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

?>


