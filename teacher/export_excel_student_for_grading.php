<?php
require '../config/conn.php';
require '../vendor_excel/autoload.php';
require '../config/myTools.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Sample student data retrieval based on POST parameters
/*
Array
(
    [0] => Array
        (
            [id] => 1
            [student_id] => 30
            [teacher_subject_id] => 47
            [read_flg] => 1
            [student_name] => Ana Garcia
        )

    [1] => Array
        (
            [id] => 2
            [student_id] => 31
            [teacher_subject_id] => 47
            [read_flg] => 1
            [student_name] => Isabelino Mabini
        )
)
*/


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacherSubject = $_POST['teacher_subject'] ?? '';
    if (empty($teacherSubject)) {
        header("Location: mysubjects.php");
        exit();
    }
    // Fetch students for the given teacher subject
    $students = myTools::getStudentsByTeacherSubject(['teacher_subject_id' => $teacherSubject, 'conn' => $conn]);
    // myTools::display(($students));exit;
    if (empty($students)) {
        $_SESSION['error'] = "No students found for the specified subject.";
        header("Location: importstudents.php?teacher_subject=" . urlencode($teacherSubject));
        exit();
    }
    // Create new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // header row is enrolee_id, student_name, grade_score (fractional number)
    $sheet->setCellValue('A1', 'enrollee_id');
    $sheet->setCellValue('B1', 'student_name');
    $sheet->setCellValue('C1', 'grade_score (fractional number)');
    // Fill data except Column C

    $rowNum = 2;
    foreach ($students as $student) {
        $sheet->setCellValue('A' . $rowNum, $student['id']);
        $sheet->setCellValue('B' . $rowNum, $student['student_name']);
        $rowNum++;
    }

    $sheet->setTitle('Students for Grading');
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="students_for_grading.xlsx"');
    header('Cache-Control: max-age=0');

    // write
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    // close browser tab after download
    echo "<script>window.close();</script>";
    exit;
}
