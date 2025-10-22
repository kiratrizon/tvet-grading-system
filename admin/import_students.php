<?php
session_start();
require '../config/conn.php';
require '../vendor_excel/autoload.php';
require '../config/myTools.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../index.php");
        exit;
    }
} else {
    header("location: ../index.php");
    exit;
}


$teacherName = $_SESSION['teacher_name'] ?? 'Unknown Teacher';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];

    if (!in_array($_FILES['file']['type'], $allowedTypes)) {
        $_SESSION['error'] = "Invalid file type. Please upload an Excel file.";
        header("Location: mysubjects.php?subject=" . urlencode($subject_code));
        exit();
    }

    $file = $_FILES['file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Expected Headers
        $expectedHeaders = ['student_email', 'student_name', 'student_program'];

        if ($data[0] !== $expectedHeaders) {
            $_SESSION['error'] = "Invalid file format. Please use the correct template.";
            header("Location: mysubjects.php?subject=" . urlencode($subject_code));
            exit();
        }

        // Remove header row
        array_shift($data);
        // map and trim
        $data = array_map(function($row) {
            return array_map('trim', $row);
        }, $data);



        // myTools::display(json_encode($data));exit;
        $added = 0;
        $skipped = 0;
        $getCourseCodes = $conn->query("SELECT id, lower(course_code) as course_code FROM courses")->fetch_all(MYSQLI_ASSOC);
        // make course_code as key and course_id as value
        $courseCodeToId = [];
        foreach ($getCourseCodes as $course) {
            $courseCodeToId[$course['course_code']] = $course['id'];
        }
        foreach ($data as $row) {
            $email = $row[0];
            $name = $row[1];
            $course = $courseCodeToId[strtolower($row[2])] ?? '';
            if ($email && $name && $course) {
                $result = myTools::registerStudents([
                    'conn' => $conn,
                    'email' => $email,
                    'name' => $name,
                    'course_id' => $course
                ]);
                if ($result['status']) {
                    $added++;
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        $_SESSION['success'] = "Import completed. Added: $added, Skipped: $skipped.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: students_add.php");
}
