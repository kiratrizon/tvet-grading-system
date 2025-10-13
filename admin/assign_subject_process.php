<?php
session_start();
require_once '../config/conn.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $course = $_POST['course'];
    $sections = $_POST['schedule_section'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $sy = $_POST['sy'];
    $schedule_days = $_POST['schedule_day'];
    $schedule_start_times = $_POST['schedule_time_start'];
    $schedule_end_times = $_POST['schedule_time_end'];

    $assigned_at = date('Y-m-d h:i A');

    $conflict_detected = false;
    $conflict_messages = [];
    $success_messages = [];

    for ($i = 0; $i < count($schedule_days); $i++) {
        $day = $schedule_days[$i];
        $start_time = $schedule_start_times[$i];
        $end_time = $schedule_end_times[$i];
        $section = isset($sections[$i]) ? $sections[$i] : '';

        // CHECK IF TEACHER ALREADY HAS THE SUBJECT FOR THE SAME SECTION
        $check_subject = $conn->query("SELECT * FROM teacher_subjects 
                                       WHERE teacher_id='$teacher_id' 
                                       AND subject_id='$subject_id' 
                                       AND section='$section'");
        if ($check_subject->num_rows > 0) {
            $_SESSION['error'] = "This subject is already assigned to the teacher for section $section.";
            header("location: asignteacher.php");
            exit;
        }

        // CHECK FOR SCHEDULE CONFLICT (DIFFERENT TEACHERS, SAME SECTION)
        $check_schedule = $conn->query("SELECT ts.schedule_day, ts.schedule_time_start, ts.schedule_time_end, s.s_descriptive_title, t.t_name, ts.section
                                        FROM teacher_subjects ts
                                        JOIN subjects s ON ts.subject_id = s.s_id
                                        JOIN teachers t ON ts.teacher_id = t.t_id
                                        WHERE ts.schedule_day = '$day' 
                                        AND ('$start_time' < ts.schedule_time_end AND '$end_time' > ts.schedule_time_start)
                                        AND ts.section = '$section'");

        if ($check_schedule->num_rows > 0) {
            $conflict_detected = true;
            while ($conflict = $check_schedule->fetch_assoc()) {
                $conflict_messages[] = "Conflict on $day: '{$conflict['s_descriptive_title']}' (Teacher: {$conflict['t_name']}, Section: {$conflict['section']}) from {$conflict['schedule_time_start']} to {$conflict['schedule_time_end']}.";
            }
        } else {
            // INSERT NEW SCHEDULE ENTRY PER DAY/TIME IF NO CONFLICT
            $conn->query("INSERT INTO `teacher_subjects` (`teacher_id`, `subject_id`, `course`, `section`, `year_level`, `semester`, `school_year`, `schedule_day`, `schedule_time_start`, `schedule_time_end`, `assigned_date`) 
                          VALUES ('$teacher_id','$subject_id','$course','$section','$year_level','$semester','$sy','$day','$start_time','$end_time','$assigned_at')");

            $success_messages[] = "Successfully assigned on $day from $start_time to $end_time (Section: $section).";
        }
    }

    if ($conflict_detected) {
        $_SESSION['error'] = implode("<br>", $conflict_messages);
    }

    if (!empty($success_messages)) {
        $_SESSION['success'] = implode("<br>", $success_messages);
    }

    header("location: asignteacher.php");
}
