<?php
session_start();
require '../config/conn.php';
require '../vendor_excel/autoload.php';
require 'send_email.php';
require '../config/myTools.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}
$teacher_id = $_SESSION['teacher_id'];


$teacherName = $_SESSION['teacher_name'] ?? 'Unknown Teacher';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];

    $teacherSubject = $_POST['teacher_subject'] ?? '';
    if (empty($teacherSubject)) {
        header("Location: mysubjects.php");
        $_SESSION['error'] = "Teacher subject not specified.";
        exit();
    }
    if (!in_array($_FILES['file']['type'], $allowedTypes)) {
        $_SESSION['error'] = "Invalid file type. Please upload an Excel file.";
        header("Location: importstudents.php?teacher_subject=" . urlencode($teacherSubject));
        exit();
    }

    $file = $_FILES['file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Expected Headers
        $expectedHeaders = ['id'];

        if ($data[0] !== $expectedHeaders) {
            $_SESSION['error'] = "Invalid file format. Please use the correct template.";
            header("Location: mysubjects.php?subject=" . urlencode($subject_code));
            exit();
        }

        // Remove header row
        array_shift($data);

        // get teacher course from teacher subject
        // subject_id then if query the subject_id into courses
        $subjectQuery = $conn->query("SELECT subject_id FROM teacher_subjects WHERE id = '$teacherSubject'");
        $subjectRow = $subjectQuery->fetch_assoc();
        $subject_id = $subjectRow['subject_id'] ?? '';
        if (empty($subject_id)) {
            header("Location: mysubjects.php");
            $_SESSION['error'] = "Subject not found for the given teacher subject.";
            exit();
        }
        $courseQuery = $conn->query("SELECT s_course, s_course_code, s_descriptive_title FROM subjects WHERE s_id = '$subject_id'");
        $courseRow = $courseQuery->fetch_assoc();

        if (empty($courseRow)) {
            header("Location: mysubjects.php");
            $_SESSION['error'] = "Course not found for the given subject.";
            exit();
        }
        // myTools::display(json_encode($courseRow));exit;
        $subjectName = $courseRow['s_course_code'] . ' - ' . $courseRow['s_descriptive_title'];
        // myTools::display(json_encode($courseRow['s_course']));exit;

        $skipped = 0;
        $imported = 0;
        $studentNames = [];
        foreach ($data as $row) {
            $studentId = trim($row[0]);
            if (empty($studentId)) {
                $skipped++;
                continue; // Skip empty IDs
            }
            // check if student already exist in teacher_subject
            $checkQuery = $conn->query("SELECT * FROM teacher_subject_enrollees WHERE student_id = '$studentId' AND teacher_subject_id = '$teacherSubject' and student_id = '$studentId'");
            if ($checkQuery->num_rows > 0) {
                $skipped++;
                continue; // Skip existing students
            }
            // check if student is in the same course as the subject
            $studentCourseQuery = $conn->query("SELECT course, name FROM student_users WHERE id = '$studentId'");
            $studentCourseRow = $studentCourseQuery->fetch_assoc();
            if (!empty($studentCourseRow) && $studentCourseRow['course'] == $courseRow['s_course']) {
                // Insert student into teacher_subject_enrollees
                $insertQuery = $conn->prepare("INSERT INTO teacher_subject_enrollees (student_id, teacher_subject_id) VALUES (?, ?)");
                $insertQuery->bind_param("ii", $studentId, $teacherSubject);
                if ($insertQuery->execute()) {
                    $imported++;
                    $studentNames[] = $studentCourseRow['name']; // Store student name
                    // close?
                    $insertQuery->close();
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        $_SESSION['success'] = "Import completed. Successfully imported: $imported. Skipped: $skipped.";
        // send mail to admin
        if ($imported > 0) {
            $adminEmails = [];
            $adminQuery = $conn->query("SELECT a_user_name FROM admin");
            while ($adminRow = $adminQuery->fetch_assoc()) {
                // verify email format
                if (filter_var($adminRow['a_user_name'], FILTER_VALIDATE_EMAIL)) {
                    $adminEmails[] = $adminRow['a_user_name'];
                }
            }
            $subject = "Students Imported by $teacherName";
            $body = "<p>Dear Admin,</p>
                     <p>The following students have been imported by <strong>$teacherName</strong> for the course <strong>$subjectName</strong>:</p>
                     <ul>";
            foreach ($studentNames as $studentName) {
                $body .= "<li>" . htmlspecialchars($studentName) . "</li>";
            }
            $body .= "</ul>
                     <p>Total Imported: $imported</p>
                     <p>Regards,<br/>Automated Notification System</p>";

            myTools::sendEmail([
                'to' => $adminEmails,
                'name' => array_fill(0, count($adminEmails), 'Admin'),
                'subject' => $subject,
                'body' => $body
            ]);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing file: " . $e->getMessage();
    }
    header("Location: importstudents.php?teacher_subject=" . urlencode($teacherSubject));
    exit();
}
