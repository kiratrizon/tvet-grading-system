<?php
session_start();
require '../config/conn.php';
require '../vendor_excel/autoload.php';
require '../config/myTools.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}
$teacher_id = $_SESSION['teacher_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['grades_file'])) {
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];

    if (!in_array($_FILES['grades_file']['type'], $allowedTypes)) {
        $_SESSION['error'] = "Invalid file type. Please upload an Excel or CSV file.";
        header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
        exit();
    }

    $criterion_id = $_POST['criterion_id'] ?? '';
    $teacher_subject_id = $_POST['teacher_subject_id'] ?? '';
    $fromNav = $_POST['from_nav'] ?? 1;
    $note_criteria = $_POST['note_criteria'] ?? '';
    $coverage = $_POST['covered'] ?? '';
    $total_items = $_POST['total_items'] ?? '';
    $_SESSION['from_nav'] = $fromNav; // store in session for redirection

    if (empty($criterion_id) || empty($teacher_subject_id) || empty($fromNav) || empty($note_criteria) || empty($coverage) || empty($total_items)) {
        $_SESSION['error'] = "Missing required information.";
        header("Location: importstudents.php?teacher_subject=" . urlencode($teacher_subject_id));
        exit();
    }

    $coverage = (int)$coverage;

    $file = $_FILES['grades_file']['tmp_name'];

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Expected Headers
        $expectedHeaders = ['enrollee_id', 'student_name', 'grade_score'];

        if ($data[0] !== $expectedHeaders) {
            $_SESSION['error'] = "Invalid file format. Please use the correct template.";
            header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
            exit();
        }

        // Remove header row
        array_shift($data);
        // map and trim
        $data = array_map(function ($row) {
            return array_map('trim', $row);
        }, $data);

        // myTools::display(($data));exit;

        // Process each row

        $fixedData = [];
        foreach ($data as $row) {
            $id = $row[0];
            $student_name = $row[1];
            $score = $row[2] ?? null;

            if (empty($score) || $score == 0) {
                $fixedData[] = [$id, $score];
                continue;
            }

            // if not numeric or it is less than 0 skip
            if (!is_numeric($score) || $score < 0) {
                $error = true;
                $_SESSION['error'] = "Invalid grade format for $student_name. Please ensure all grades are numeric and non-negative.";
                header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
            }
            $fixedData[] = [$id, $score];
        }

        // insert criteria record note first and get the inserted id from criteria_note_records
        $criteriaNoteId = $conn->query("INSERT INTO criteria_note_records (grading_criterion_id, note, period, total_item) VALUES ('$criterion_id', '" . $conn->real_escape_string($note_criteria) . "', '$coverage', '$total_items')");
        $criteria_note_id = $conn->insert_id;
        if (empty($criteria_note_id)) {
            $_SESSION['error'] = "Failed to save criteria note.";
            header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
            exit();
        }
        $buildValues = [];

        foreach ($fixedData as $row) {
            $enrollee_id = $row[0];
            $grade_score = $row[1];

            $buildValues[] = "('$criteria_note_id', '$grade_score', '$enrollee_id')";
        }

        if (!empty($buildValues)) {
            // Insert grade record
            $conn->query("INSERT INTO criteria_grades (criteria_note_record_id, score, enrollee_id) VALUES " . implode(", ", $buildValues));
        } else {
            throw new Exception("No valid grade data to insert.");
        }

        $_SESSION['success'] = "Grades imported successfully.";
        header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing file: " . $e->getMessage();
        header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
        exit();
    }
} else {
    header("Location: importstudents.php?teacher_subject=" . urlencode($_POST['teacher_subject_id']));
    exit();
}
