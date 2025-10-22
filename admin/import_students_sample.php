<?php

require_once '../config/myTools.php';


$params = [];

$filename = 'add_students_template.xlsx';
$headers = ['student_email', 'student_name', 'student_program'];
$title = 'Add Students Template';
myTools::exportExcelTemplate([
    'headers' => $headers,
    'filename' => $filename,
    'title' => $title
]);

// close browser tab after download
echo '<script>window.close();</script>';
exit;
