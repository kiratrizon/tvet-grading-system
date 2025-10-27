<?php

require_once '../config/myTools.php';

$params = [];

$params['headers'] = ['student_id'];
$params['title'] = 'Sample Student Enrollment Template';
$params['filename'] = 'student_enrollment_template.xlsx';
myTools::exportExcelTemplate($params);

// close browser tab after download
echo "<script>window.close();</script>";
exit;
