<?php
session_start();
require_once '../config/conn.php';
require_once '../config/myTools.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $school_year = $_POST['sy'];

    $schedules = [
        1 => ['start' => $_POST['schedule_time_start_1'] ?? null, 'end' => $_POST['schedule_time_end_1'] ?? null, 'prefix' => 'm_'],
        2 => ['start' => $_POST['schedule_time_start_2'] ?? null, 'end' => $_POST['schedule_time_end_2'] ?? null, 'prefix' => 't_'],
        3 => ['start' => $_POST['schedule_time_start_3'] ?? null, 'end' => $_POST['schedule_time_end_3'] ?? null, 'prefix' => 'w_'],
        4 => ['start' => $_POST['schedule_time_start_4'] ?? null, 'end' => $_POST['schedule_time_end_4'] ?? null, 'prefix' => 'th_'],
        5 => ['start' => $_POST['schedule_time_start_5'] ?? null, 'end' => $_POST['schedule_time_end_5'] ?? null, 'prefix' => 'f_'],
        6 => ['start' => $_POST['schedule_time_start_6'] ?? null, 'end' => $_POST['schedule_time_end_6'] ?? null, 'prefix' => 's_'],
        7 => ['start' => $_POST['schedule_time_start_7'] ?? null, 'end' => $_POST['schedule_time_end_7'] ?? null, 'prefix' => 'ss_'],
    ];

    $assigned_at = date('Y-m-d h:i A');

    $arrangeSchedules = [];

    foreach ($schedules as $key => $schedule) {
        if (empty($schedule['start']) || empty($schedule['end'])) {
            continue;
        }
        $arrangeSchedules[$key] = $schedule;
    }
    // myTools::display(compact('teacher_id', 'subject_id', 'course', 'year_level', 'semester', 'school_year', 'arrangeSchedules'));
    // exit;

    $conflict_messages = [];
    $success_messages = [];

    foreach ($arrangeSchedules as $key => $val) {
        $start = $val['start'];
        $end = $val['end'];
        $prefix = $val['prefix'];
        $keyStart =  $prefix . 'start';
        $keyEnd = $prefix . 'end';

        $queryConflict = $conn->query(
            "SELECT * from teacher_subjects where teacher_id = '$teacher_id' and semester = '$semester' and school_year = '$school_year' and ($keyStart >= '$start' and $keyEnd < '$start') or ($keyStart > '$end' and $keyEnd <= '$end')"
        )->fetch_all(MYSQLI_ASSOC);

        if (empty($queryConflict)) {
            // insert
            $conn->query("INSERT into teacher_subjects(teacher_id, subject_id, course, year_level, semester, school_year, $keyStart, $keyEnd, assigned_date) values('$teacher_id', '$subject_id', '$course', '$year_level', '$semester', '$school_year', '$start', '$end', '$assigned_at')");
        } else {
        }
    }

    if (!$conflict_messages) {
        $success_messages[] = "Saved!";
    }

    if (!empty($success_messages)) {
        $_SESSION['success'] = implode("<br>", $success_messages);
    }

    header("location: asignteacher.php");
}
