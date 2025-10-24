<?php
require '../config/conn.php';
require '../config/myTools.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_subject = $_POST['teacher_subject_id'] ?? null;
    if (!$teacher_subject) {
        http_response_code(400);
        exit;
    }

    $available_periods = myTools::getAvailablePeriodsByTeacherSubjectID([
        'conn' => $conn,
        'teacher_subject_id' => $teacher_subject
    ]);

    echo json_encode($available_periods);
    exit;
}
