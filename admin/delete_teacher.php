<?php
session_start();
require_once  '../config/conn.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];


    mysqli_begin_transaction($conn);

    try {
        // Delete related records
        $queries = [
            "DELETE FROM student_grades WHERE teacher_id = ?",
            "DELETE FROM teacher_subjects WHERE teacher_id = ?",
            "DELETE FROM web_users WHERE email = (SELECT t_user_name FROM teachers WHERE t_id = ?)",
            "DELETE FROM teachers WHERE t_id = ?"
        ];

        foreach ($queries as $query) {
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $teacher_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($conn);
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["success" => false]);
    }

    mysqli_close($conn);
}
