<?php

require_once '../config/conn.php';

$id = $_GET['id'];

$subjects = $conn->query("
    SELECT subjects.*, courses.course_code , courses.course_name
    FROM subjects 
    LEFT JOIN courses ON subjects.s_course = courses.id 
    WHERE subjects.s_id = $id
");

$subjects = $subjects->fetch_array(MYSQLI_ASSOC);

echo json_encode($subjects);
