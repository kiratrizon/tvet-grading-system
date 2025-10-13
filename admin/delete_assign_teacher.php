<?php
session_start();
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_id']) && isset($_POST['subject_id']) && isset($_POST['teacher_id'])) {
    $assign_id = $_POST['assign_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];

    mysqli_begin_transaction($conn);

    try {
        $get_ids_query = "SELECT id FROM teacher_subjects WHERE subject_id = ? AND teacher_id = ?";
        $stmt = mysqli_prepare($conn, $get_ids_query);
        mysqli_stmt_bind_param($stmt, "ii", $subject_id, $teacher_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $assign_ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $assign_ids[] = $row['id'];
        }
        mysqli_stmt_close($stmt);

        if (!empty($assign_ids)) {
            $delete_query = "DELETE FROM teacher_subjects WHERE subject_id = ? AND teacher_id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "ii", $subject_id, $teacher_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $check_teachers_query = "SELECT COUNT(*) FROM teacher_subjects WHERE subject_id = ?";
        $stmt = mysqli_prepare($conn, $check_teachers_query);
        mysqli_stmt_bind_param($stmt, "i", $subject_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $remaining_teachers);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($remaining_teachers == 0) {
            $delete_grades_query = "DELETE FROM student_grades WHERE subject_id = ?";
            $stmt = mysqli_prepare($conn, $delete_grades_query);
            mysqli_stmt_bind_param($stmt, "i", $subject_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($conn);
        echo json_encode([
            "success" => true,
            "message" => "Teacher assignment deleted successfully. " .
                ($remaining_teachers == 0 ? "Associated student grades were also removed." : "Other teachers are still assigned to this subject.")
        ]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["success" => false, "message" => "An error occurred while deleting."]);
    }

    mysqli_close($conn);
}
