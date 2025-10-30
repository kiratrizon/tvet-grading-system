<?php

require_once '../config/myTools.php';


$params = [];

$filename = 'enrollment_template.xlsx';
$headers = ['student_name', 'student_email(optional)', 'program_code', 'year_level', 'school_year'];
$title = 'Enrollment Template';
myTools::exportExcelTemplate([
    'headers' => $headers,
    'filename' => $filename,
    'title' => $title
]);

// close browser tab after download
echo '<script>window.close();</script>';
exit;
