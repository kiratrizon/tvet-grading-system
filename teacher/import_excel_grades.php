<?php
session_start();
require '../config/conn.php';
require '../vendor_excel/autoload.php';
require 'send_email.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}
$teacher_id = $_SESSION['teacher_id'];


$teacherName = $_SESSION['teacher_name'] ?? 'Unknown Teacher';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $subject_code = $_POST['subject_code'];
    $course = $_POST['course'];
    $course_id = $_POST['course_id'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $school_year = $_POST['school_year'];
    $subject_id = $_POST['subject_id'];
    $section = $_POST['section'];

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
        $expectedHeaders = ['name', 'course', 'course_code', 'descriptive_title', 'year_level', 'semester', 'school_year', 'final_rating', 'remarks', 'section'];

        if ($data[0] !== $expectedHeaders) {
            $_SESSION['error'] = "Invalid file format. Please use the correct template.";
            header("Location: mysubjects.php?subject=" . urlencode($subject_code));
            exit();
        }

        // Remove header row
        array_shift($data);

        $importedCount = 0;
        $skippedCount = 0;
        $invalidCount = 0;
        $invalidRows = [];

        foreach ($data as $index => $row) {
            list($name, $importedCourse, $importedCourseCode, $importedTitle, $importedYearLevel, $importedSemester, $importedSchoolYear, $final_rating, $remarks) = $row;

            // **ERROR CHECKING:**
            if ($importedCourseCode !== $subject_code || $importedYearLevel !== $year_level || $importedSemester !== $semester || $importedSchoolYear !== $school_year) {
                $invalidCount++;
                $invalidRows[] = "Row " . ($index + 2) . " (Name: $name, Course: $importedCourse, Code: $importedCourseCode, Year: $importedYearLevel, Sem: $importedSemester, SY: $importedSchoolYear)";
                continue; // Skip invalid rows
            }

            // **1. CHECK IF STUDENT USER ALREADY EXISTS**
            $stmt = $conn->prepare("SELECT id FROM student_users WHERE name = ? AND course = ?");
            $stmt->bind_param("ss", $name, $course_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($student_id);
            $stmt->fetch();

            if ($stmt->num_rows === 0) {
                // **INSERT NEW STUDENT USER (DEFAULT EMAIL & PASSWORD)**
                $default_email = strtolower(str_replace(' ', '', $name)) . "@school.edu";
                $default_password = password_hash("student123", PASSWORD_DEFAULT);

                $insertUser = $conn->prepare("INSERT INTO student_users (name, course, email, password) VALUES (?, ?, ?, ?)");
                $insertUser->bind_param("ssss", $name, $course_id, $default_email, $default_password);
                $insertUser->execute();


                $student_id = $insertUser->insert_id;
                $insertUser->close();
            }

            $stmt->close();

            // **2. CHECK IF STUDENT GRADE ALREADY EXISTS**
            $stmt = $conn->prepare("SELECT id FROM student_grades WHERE name = ? AND course_code = ? AND school_year = ? AND semester = ?");
            $stmt->bind_param("ssss", $name, $importedCourseCode, $importedSchoolYear, $importedSemester);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                // **INSERT GRADE**
                $insert = $conn->prepare("INSERT INTO student_grades (student_id, name, course, course_code, descriptive_title, year_level, semester, school_year, final_rating, remarks, teacher_id, subject_id,section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param("isssssssssiss", $student_id, $name, $course_id, $importedCourseCode, $importedTitle, $importedYearLevel, $importedSemester, $importedSchoolYear, $final_rating, $remarks, $teacher_id, $subject_id, $section);
                $insert->execute();
                $insert->close();
                $importedCount++;
            } else {
                $skippedCount++;
            }

            $stmt->close();
        }

        if ($invalidCount > 0) {
            $_SESSION['error'] = "Import failed for $invalidCount row(s). Mismatched data found:<br><ul><li>" . implode("</li><li>", $invalidRows) . "</li></ul>";
            header("Location: mysubjects.php?subject=" . urlencode($subject_code));
            exit();
        }

        if ($importedCount > 0) {
            sendAdminNotification($teacherName, $subject_code, $importedCount);
        }

        $_SESSION['success'] = "Students imported successfully! Imported: <strong>$importedCount</strong>, Skipped: <strong>$skippedCount</strong>";
        header("Location: mysubjects.php?subject=" . urlencode($subject_code));
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while processing the file. Please try again.";
        header("Location: mysubjects.php?subject=" . urlencode($subject_code));
        exit();
    }
}
