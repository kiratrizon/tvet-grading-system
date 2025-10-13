<?php
session_start();
require_once '../config/conn.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $schedule_ids = $_POST['schedule_ids'];
    $schedule_days = $_POST['schedule_days'];
    $schedule_time_start = $_POST['schedule_time_start'];
    $schedule_time_end = $_POST['schedule_time_end'];
    $sections = $_POST['section'];
    // echo $sections;
    // exit;

    $conflict_detected = false;
    $conflict_messages = [];
    $success_messages = [];

    for ($i = 0; $i < count($schedule_ids); $i++) {
        $schedule_id = $schedule_ids[$i];
        $day = $schedule_days[$i];
        $start_time = $schedule_time_start[$i];
        $end_time = $schedule_time_end[$i];
        $section = $sections[$i];

        // CHECK FOR SCHEDULE CONFLICT (DIFFERENT TEACHERS, SAME SECTION)
        $check_schedule = $conn->query("SELECT ts.schedule_day, ts.schedule_time_start, ts.schedule_time_end, s.s_descriptive_title, t.t_name, ts.section
                                        FROM teacher_subjects ts
                                        JOIN subjects s ON ts.subject_id = s.s_id
                                        JOIN teachers t ON ts.teacher_id = t.t_id
                                        WHERE ts.schedule_day = '$day' 
                                        AND ('$start_time' < ts.schedule_time_end AND '$end_time' > ts.schedule_time_start)
                                        AND ts.section = '$section'
                                        AND ts.id != '$schedule_id'"); // Exclude the current entry being updated

        if ($check_schedule->num_rows > 0) {
            $conflict_detected = true;
            while ($conflict = $check_schedule->fetch_assoc()) {
                $conflict_messages[] = "Conflict on $day: '{$conflict['s_descriptive_title']}' (Teacher: {$conflict['t_name']}, Section: {$conflict['section']}) from {$conflict['schedule_time_start']} to {$conflict['schedule_time_end']}.";
            }
        } else {
            // UPDATE SCHEDULE ENTRY IF NO CONFLICT
            $update_query = $conn->prepare("UPDATE teacher_subjects SET schedule_day = ?, schedule_time_start = ?, schedule_time_end = ?, section = ? WHERE id = ?");
            $update_query->bind_param("ssssi", $day, $start_time, $end_time, $section, $schedule_id);
            $update_query->execute();

            $success_messages[] = "Successfully updated schedule on $day from $start_time to $end_time (Section: $section).";
        }
    }

    if ($conflict_detected) {
        $_SESSION['error'] = implode("<br>", $conflict_messages);
    }

    if (!empty($success_messages)) {
        $_SESSION['success'] = implode("<br>", $success_messages);
    }

    header("location: asignteacher.php");
    exit();
}
