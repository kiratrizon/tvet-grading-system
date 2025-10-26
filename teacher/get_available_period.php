<?php
require '../config/conn.php';
require '../config/myTools.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_subject = $_POST['teacher_subject_id'] ?? null;
    if (!$teacher_subject) {
        http_response_code(400);
        exit;
    }

    // check first if there is students 

    if ($conn->query("SELECT count(id) as cnt FROM `teacher_subject_enrollees` where teacher_subject_id = '$teacher_subject'")->fetch_assoc()['cnt'] == 0) {
        http_response_code(400);
        echo "No Enrolled Students";
        exit;
    }

    $available_periods = myTools::getAvailablePeriodsByTeacherSubjectID([
        'conn' => $conn,
        'teacher_subject_id' => $teacher_subject
    ]);

    echo json_encode($available_periods);
    exit;
}
