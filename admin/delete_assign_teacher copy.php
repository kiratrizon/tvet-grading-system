<?php
session_start();
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_id']) && isset($_POST['subject_id'])) {
    $assign_id = $_POST['assign_id'];
    $subject_id = $_POST['subject_id'];

    mysqli_begin_transaction($conn);

    try {
        // Check if there are uploaded grades for this subject
        $check_grades_query = "SELECT COUNT(*) FROM student_grades WHERE subject_id = ?";
        $stmt = mysqli_prepare($conn, $check_grades_query);
        mysqli_stmt_bind_param($stmt, "i", $subject_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $grades_count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // If grades exist, delete them
        if ($grades_count > 0) {
            $delete_grades_query = "DELETE FROM student_grades WHERE subject_id = ?";
            $stmt = mysqli_prepare($conn, $delete_grades_query);
            mysqli_stmt_bind_param($stmt, "i", $subject_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Delete the subject assignment from teacher_subjects
        $delete_assignment_query = "DELETE FROM teacher_subjects WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_assignment_query);
        mysqli_stmt_bind_param($stmt, "i", $assign_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        echo json_encode(["success" => true, "message" => "Teacher assignment deleted successfully. " . ($grades_count > 0 ? "Associated student grades were also removed." : "No student grades found.")]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["success" => false, "message" => "An error occurred while deleting."]);
    }

    mysqli_close($conn);
}
