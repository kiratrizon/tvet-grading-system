<?php

require_once '../config/conn.php';

$id = $_GET['id'];

$subjects = $conn->query("
    SELECT subjects.*, programs.program_code AS course_code , programs.program_name AS course_name
    FROM subjects 
    LEFT JOIN programs ON subjects.s_course = programs.id 
    WHERE subjects.s_id = $id
");

$subjects = $subjects->fetch_array(MYSQLI_ASSOC);

echo json_encode($subjects);
