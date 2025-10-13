<?php
session_start();
require_once '../config/conn.php';

$id = $_GET['id'];

$assignTeachers = $conn->query("SELECT 
        teachers.t_id AS teacher_id,
        teachers.t_name, 
        subjects.s_descriptive_title, 
        subjects.s_course, 
        courses.course_name,  
        subjects.s_course_code, 
        subjects.s_year_level, 
        subjects.s_semester, 
        teacher_subjects.course, 
        teacher_subjects.year_level, 
        teacher_subjects.semester,
        teacher_subjects.id,
        teacher_subjects.school_year,
        teacher_subjects.assigned_date,
        teacher_subjects.schedule_day,
        teacher_subjects.schedule_time_start,
        teacher_subjects.schedule_time_end
    FROM teacher_subjects
    JOIN teachers ON teacher_subjects.teacher_id = teachers.t_id
    JOIN subjects ON teacher_subjects.subject_id = subjects.s_id
    JOIN courses ON subjects.s_course = courses.id 
    WHERE teacher_subjects.id = $id");

$assignTeachers = $assignTeachers->fetch_array(MYSQLI_ASSOC);

// Send data to JavaScript
echo json_encode($assignTeachers);
