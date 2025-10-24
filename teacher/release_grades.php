<?php
session_start();
require '../config/conn.php';
require '../config/myTools.php';

// if no active session, http 404
if (!isset($_SESSION['teacher_id'])) {
    http_response_code(404);
    exit;
}

// required $_POST
/*
Array
(
    [teacher_subject_id] => 47
    [period] => 1
)
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $teacher_subject_id = $_POST['teacher_subject_id'] ?? null;
    $period = $_POST['period'] ?? null;
    if (empty($teacher_subject_id) || empty($period)) {
        http_response_code(404);
        exit;
    }
    // release grades
    $release = myTools::releaseGrades([
        'conn' => $conn,
        'teacher_subject_id' => $teacher_subject_id,
        'period' => $period
    ]);

    if ($release) {
        echo 'success';
        exit;
    } else {
        http_response_code(500);
        exit;
    }
} else {
    http_response_code(404);
    exit;
}
